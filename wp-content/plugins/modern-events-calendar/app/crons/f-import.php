<?php
/**
 *  WordPress initializing
 */
function mec_find_wordpress_base_path()
{
    $dir = dirname(__FILE__);
    
    do
    {
        if(file_exists($dir.'/wp-config.php')) return $dir;
    }
    while($dir = realpath($dir.'/..'));
    
    return NULL;
}

define('BASE_PATH', mec_find_wordpress_base_path().'/');
define('WP_USE_THEMES', false);

global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require(BASE_PATH.'wp-load.php');

// MEC libraries
$main = MEC::getInstance('app.libraries.main');
$db = $main->getDB();

// Get MEC IX options
$ix = $main->get_ix_options();

// Auto sync is disabled
if(!isset($ix['sync_f_import']) or (isset($ix['sync_f_import']) and !$ix['sync_f_import'])) exit(__('Auto Facebook import is disabled!', 'mec'));

$fb_page_link = isset($ix['facebook_import_page_link']) ? $ix['facebook_import_page_link'] : NULL;
if(!trim($fb_page_link)) exit(__("Please insert your facebook page's link.", 'mec'));

$fb_access_token = '1819770188280256|GyNKicqC8aT4Z7GVz_PptY-7kQQ';

$fb_page_result = $main->get_web_page('https://graph.facebook.com/v2.8/?access_token='.$fb_access_token.'&id='.$fb_page_link);
$fb_page = json_decode($fb_page_result, true);

$fb_page_id = isset($fb_page['id']) ? $fb_page['id'] : 0;
if(!$fb_page_id) exit(__("We couldn't recognize your Facebook page. Please check it and provide us a valid facebook page link.", 'mec'));

$next_page = 'https://graph.facebook.com/v2.8/'.$fb_page_id.'/events/?access_token='.$fb_access_token;

// MEC File
$file = $main->getFile();
$wp_upload_dir = wp_upload_dir();

// Imported Events
$posts = array();

do
{
    $events_result = $main->get_web_page($next_page);
    $fb_events = json_decode($events_result, true);

    // Exit the loop if no event found
    if(!isset($fb_events['data'])) break;

    foreach($fb_events['data'] as $fb_event)
    {
        $events_result = $main->get_web_page('https://graph.facebook.com/v2.8/'.$fb_event['id'].'?access_token='.$fb_access_token);
        $event = json_decode($events_result, true);

        // Event organizer
        $organizer_id = 1;

        // Event location
        $location = isset($event['place']) ? $event['place'] : array();
        
        $location_name = $location['name'];
        $location_address = trim($location_name.' '.(isset($location['location']['city']) ? $location['location']['city'] : '').' '.(isset($location['location']['state']) ? $location['location']['state'] : '').' '.(isset($location['location']['country']) ? $location['location']['country'] : '').' '.(isset($location['location']['zip']) ? $location['location']['zip'] : ''), '');
        $location_id = $main->save_location(array
        (
            'name'=>trim($location_name),
            'address'=>$location_address,
            'latitude'=>$location['location']['latitude'],
            'longitude'=>$location['location']['longitude'],
        ));

        // Event Title and Content
        $title = $event['name'];
        $description = isset($event['description']) ? $event['description'] : '';

        $start_timestamp = strtotime($event['start_time']);
        $start_date = date('Y-m-d', $start_timestamp);
        $start_hour = date('g', $start_timestamp);
        $start_minutes = date('i', $start_timestamp);
        $start_ampm = date('A', $start_timestamp);

        $end_timestamp = isset($event['end_time']) ? strtotime($event['end_time']) : 0;
        $end_date = $end_timestamp ? date('Y-m-d', $end_timestamp) : $start_date;
        $end_hour = $end_timestamp ? date('g', $end_timestamp) : 8;
        $end_minutes = $end_timestamp ? date('i', $end_timestamp) : '00';
        $end_ampm = $end_timestamp ? date('A', $end_timestamp) : 'PM';

        // Event Time Options
        $allday = 0;

        $args = array
        (
            'title'=>$title,
            'content'=>$description,
            'location_id'=>$location_id,
            'organizer_id'=>$organizer_id,
            'date'=>array
            (
                'start'=>array(
                    'date'=>$start_date,
                    'hour'=>$start_hour,
                    'minutes'=>$start_minutes,
                    'ampm'=>$start_ampm,
                ),
                'end'=>array(
                    'date'=>$end_date,
                    'hour'=>$end_hour,
                    'minutes'=>$end_minutes,
                    'ampm'=>$end_ampm,
                ),
                'repeat'=>array(),
                'allday'=>$allday,
                'comment'=>'',
                'hide_time'=>0,
                'hide_end_time'=>0,
            ),
            'start'=>$start_date,
            'start_time_hour'=>$start_hour,
            'start_time_minutes'=>$start_minutes,
            'start_time_ampm'=>$start_ampm,
            'end'=>$end_date,
            'end_time_hour'=>$end_hour,
            'end_time_minutes'=>$end_minutes,
            'end_time_ampm'=>$end_ampm,
            'repeat_status'=>0,
            'repeat_type'=>'',
            'interval'=>NULL,
            'finish'=>$end_date,
            'year'=>NULL,
            'month'=>NULL,
            'day'=>NULL,
            'week'=>NULL,
            'weekday'=>NULL,
            'weekdays'=>NULL,
            'meta'=>array
            (
                'mec_source'=>'facebook-calendar',
                'mec_facebook_page_id'=>$fb_page_id,
                'mec_facebook_event_id'=>$fb_event['id'],
            )
        );

        $post_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='".$fb_event['id']."' AND `meta_key`='mec_facebook_event_id'", 'loadResult');

        // Insert the event into MEC
        $post_id = $main->save_event($args, $post_id);
        
        // Add it to the imported posts
        $posts[] = $post_id;
        
        // Set location to the post
        if($location_id) wp_set_object_terms($post_id, (int) $location_id, 'mec_location');

        $photos_results = $main->get_web_page('https://graph.facebook.com/v2.8/'.$fb_event['id'].'/photos?access_token='.$fb_access_token);
        $photos = json_decode($photos_results, true);

        if(isset($photos['data']) and is_array($photos['data']) and count($photos['data']))
        {
            $photo = $main->get_web_page('https://graph.facebook.com/'.$photos['data'][0]['id'].'/picture?type=normal');
            $file_name = md5($post_id).'.'.$main->get_image_type_by_buffer($photo);

            $path = rtrim($wp_upload_dir['path'], DS.' ').DS.$file_name;
            $url = rtrim($wp_upload_dir['url'], '/ ').'/'.$file_name;

            $file->write($path, $photo);
            $main->set_featured_image($url, $post_id);
        }
    }

    $next_page = isset($fb_events['paging']['next']) ? $fb_events['paging']['next'] : NULL;
}
while($next_page);

echo sprintf(__('%s facebook events imported/updated.', 'mec'), count($posts));
exit;