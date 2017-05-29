<?php
/** no direct access **/
defined('_MECEXEC_') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) or isset($styling['color'])) ? 'colorskin-custom' : '';
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-slider-<?php echo $this->style; ?>-wrap" >
        <div class='mec-slider-<?php echo $this->style; ?> owl-carousel owl-theme'>
            <?php
                foreach($this->events as $date):
                foreach($date as $event):
                
                // Skip to next event if there is no image
                if(empty($event->data->thumbnails['large'])) continue;

                // get src attr from img
                $doc = new DOMDocument();
                $doc->loadHTML($event->data->thumbnails['large']);
                $xpath = new DOMXPath($doc);
                $src = $xpath->evaluate("string(//img/@src)"); 

                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();

                $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
            ?>
                <?php if($this->style == 't1'): ?>
                    <article class="mec-event-article mec-clear">

                        <div class="mec-slider-t1-img" style="background: url(<?php echo $src; ?> );"></div>

                        <div class="mec-slider-t1-content mec-event-grid-modern">
                        
                            <div class="event-grid-modern-head clearfix">
                                <div class="mec-event-date mec-color"><?php echo date_i18n($this->date_format_type1_1, strtotime($event->date['start']['date'])); ?></div>
                                <div class="mec-event-month"><?php echo date_i18n($this->date_format_type1_2, strtotime($event->date['start']['date'])); ?></div>
                                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
                                <div class="mec-event-day"><?php echo date_i18n($this->date_format_type1_3, strtotime($event->date['start']['date'])); ?></div>
                            </div>
                            <div class="mec-event-content">
                            <h4 class="mec-event-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : '') . (isset($location['address']) ? ' | '.$location['address'] : ''); ?></div>
                            </div>
                            <div class="mec-event-footer">
                                <a class="mec-booking-button" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php (is_array($event->data->tickets) and count($event->data->tickets)) ? _e('REGISTER', 'mec') : _e('View Detail', 'mec'); ?></a>
                            </div>
                        </div>
                    </article>
                <?php elseif($this->style == 't2'): ?>
                    <article class="mec-event-article mec-clear">

                        <div class="mec-slider-t2-img" style="background: url(<?php echo $src; ?> );"></div>
                        <div class="mec-slider-t2-content mec-event-grid-modern mec-bg-color">

                            <div class="event-grid-modern-head clearfix">
                                <div class="mec-event-date mec-color"><?php echo date_i18n($this->date_format_type2_1, strtotime($event->date['start']['date'])); ?></div>
                                <div class="mec-event-month"><?php echo date_i18n($this->date_format_type2_2, strtotime($event->date['start']['date'])); ?></div>
                                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
                                <div class="mec-event-day"><?php echo date_i18n($this->date_format_type2_3, strtotime($event->date['start']['date'])); ?></div>
                            </div>
                            <div class="mec-event-content">
                                <h4 class="mec-event-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : '') . (isset($location['address']) ? ' | '.$location['address'] : ''); ?></div>
                            </div>
                            <div class="mec-event-footer">
                                <a class="mec-booking-button" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php (is_array($event->data->tickets) and count($event->data->tickets)) ? _e('REGISTER', 'mec') : _e('View Detail', 'mec'); ?></a>
                            </div>
                        </div>
                    </article>
                <?php elseif($this->style == 't3'): ?>
                    <article class="mec-event-article mec-clear">
                        <div class="mec-slider-t3-img" style="background: url(<?php echo $src; ?> );"></div>
                        <div class="mec-slider-t3-content mec-event-grid-modern">

                            <div class="event-grid-modern-head clearfix">
                                <div class="mec-event-date mec-color"><?php echo date_i18n($this->date_format_type3_1, strtotime($event->date['start']['date'])); ?></div>
                                <div class="mec-event-month"><?php echo date_i18n($this->date_format_type3_2, strtotime($event->date['start']['date'])); ?></div>
                                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
                                <div class="mec-event-day"><?php echo date_i18n($this->date_format_type3_3, strtotime($event->date['start']['date'])); ?></div>
                            </div>
                            <div class="mec-event-content">
                                <h4 class="mec-event-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : '') . (isset($location['address']) ? ' | '.$location['address'] : ''); ?></div>
                            </div>
                            <div class="mec-slider-t3-footer">
                                <a class="mec-booking-button" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php (is_array($event->data->tickets) and count($event->data->tickets)) ? _e('REGISTER', 'mec') : _e('View Detail', 'mec'); ?></a>
                            </div>
                        </div>
                    </article>
                <?php elseif($this->style == 't4'): ?>
                <article class="mec-event-article mec-clear">
                    <div class="mec-slider-t4-img" style="background: url(<?php echo $src; ?> );"></div>
                    <div class="mec-slider-t4-content mec-event-grid-modern">

                        <div class="event-grid-modern-head clearfix">
                            <div class="mec-event-date mec-color"><?php echo date_i18n($this->date_format_type4_1, strtotime($event->date['start']['date'])); ?></div>
                            <div class="mec-event-month"><?php echo date_i18n($this->date_format_type4_2, strtotime($event->date['start']['date'])); ?></div>
                            <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
                            <div class="mec-event-day"><?php echo date_i18n($this->date_format_type4_3, strtotime($event->date['start']['date'])); ?></div>
                        </div>
                        <div class="mec-event-content">
                            <h4 class="mec-event-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                            <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : '') . (isset($location['address']) ? ' | '.$location['address'] : ''); ?></div>
                        </div>
                        <div class="mec-slider-t4-footer">
                            <a class="mec-booking-button" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php (is_array($event->data->tickets) and count($event->data->tickets)) ? _e('REGISTER', 'mec') : _e('View Detail', 'mec'); ?></a>
                        </div>
                    </div>
                </article>
                <?php endif; ?>

            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
	</div>
</div>