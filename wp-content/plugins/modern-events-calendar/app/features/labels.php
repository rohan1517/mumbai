<?php
/** no direct access **/
defined('_MECEXEC_') or die();

/**
 * Webnus MEC labels class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_labels extends MEC_base
{
    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();
    }
    
    /**
     * Initialize label feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        $this->factory->action('init', array($this, 'register_taxonomy'), 15);
        $this->factory->action('mec_label_edit_form_fields', array($this, 'edit_form'));
        $this->factory->action('mec_label_add_form_fields', array($this, 'add_form'));
        $this->factory->action('edited_mec_label', array($this, 'save_metadata'));
        $this->factory->action('created_mec_label', array($this, 'save_metadata'));
        
        $this->factory->action('add_meta_boxes', array($this, 'register_meta_boxes'));
        
        $this->factory->filter('manage_edit-mec_label_columns', array($this, 'filter_columns'));
        $this->factory->filter('manage_mec_label_custom_column', array($this, 'filter_columns_content'), 10, 3);
        
        $this->factory->action('save_post', array($this, 'save_event'), 3);
    }
    
    /**
     * Register label taxonomy
     * @author Webnus <info@webnus.biz>
     */
    public function register_taxonomy()
    {
        register_taxonomy(
            'mec_label',
            $this->main->get_main_post_type(),
            array(
                'label'=>__('Labels', 'mec'),
                'labels'=>array(
                    'name'=>__('Labels', 'mec'),
                    'singular_name'=>__('Label', 'mec'),
                    'all_items'=>__('All Labels', 'mec'),
                    'edit_item'=>__('Edit Label', 'mec'),
                    'view_item'=>__('View Label', 'mec'),
                    'update_item'=>__('Update Label', 'mec'),
                    'add_new_item'=>__('Add New Label', 'mec'),
                    'new_item_name'=>__('New Label Name', 'mec'),
                    'popular_items'=>__('Popular Labels', 'mec'),
                    'search_items'=>__('Search Labels', 'mec'),
                ),
                'rewrite'=>array('slug'=>'events-label'),
                'public'=>false,
                'show_ui'=>true,
                'hierarchical'=>false,
            )
        );
        
        register_taxonomy_for_object_type('mec_label', $this->main->get_main_post_type());
    }
    
    /**
     * Show edit form of labels
     * @author Webnus <info@webnus.biz>
     * @param object $term
     */
    public function edit_form($term)
    {
        $color = get_metadata('term', $term->term_id, 'color', true);
    ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="mec_color"><?php _e('Color', 'mec'); ?></label>
            </th>
            <td>
                <input type="text" name="color" id="mec_color" value="<?php echo $color; ?>" data-default-color="<?php echo $color; ?>" class="mec-color-picker" />
                <p class="description"><?php _e('Select label color', 'mec'); ?></p>
            </td>
        </tr>
    <?php
    }
    
    /**
     * Show add form of labels
     * @author Webnus <info@webnus.biz>
     */
    public function add_form()
    {
    ?>
        <div class="form-field">
            <label for="mec_color"><?php _e('Color', 'mec'); ?></label>
            <input type="text" name="color" id="mec_color" value="" data-default-color="<?php echo $this->main->get_default_label_color(); ?>" class="mec-color-picker" />
            <p class="description"><?php _e('Select label color', 'mec'); ?></p>
        </div>
    <?php
    }
    
    /**
     * Save label meta data
     * @author Webnus <info@webnus.biz>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        $color = isset($_POST['color']) ? $_POST['color'] : $this->main->get_default_label_color();
        update_term_meta($term_id, 'color', $color);
    }
    
    /**
     * Filter label taxonomy columns
     * @author Webnus <info@webnus.biz>
     * @param array $columns
     * @return array
     */
    public function filter_columns($columns)
    {
        unset($columns['name']);
        unset($columns['slug']);
        unset($columns['description']);
        unset($columns['posts']);
        
        $columns['id'] = __('ID', 'mec');
        $columns['name'] = __('Name', 'mec');
        $columns['color'] = __('Color', 'mec');
        $columns['posts'] = __('Count', 'mec');
        $columns['slug'] = __('Slug', 'mec');

        return $columns;
    }
    
    /**
     * Filter content of label taxonomy
     * @author Webnus <info@webnus.biz>
     * @param string $content
     * @param string $column_name
     * @param int $term_id
     * @return string
     */
    public function filter_columns_content($content, $column_name, $term_id)
    {
        switch($column_name)
        {
            case 'id':
                
                $content = $term_id;
                break;

            case 'color':
                
                $content = '<span class="mec-color" style="background-color: '.get_metadata('term', $term_id, 'color', true).';"></span>';
                break;

            default:
                break;
        }

        return $content;
    }
    
    /**
     * Register meta box of labels
     * @author Webnus <info@webnus.biz>
     */
    public function register_meta_boxes()
    {
        add_meta_box('mec_metabox_label', __('Event Labels', 'mec'), array($this, 'meta_box_labels'), $this->main->get_main_post_type(), 'side');
    }
    
    /**
     * Show meta box of labels
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_labels($post)
    {
        $labels = get_terms('mec_label', array('orderby'=>'count', 'order'=>'DESC', 'hide_empty'=>'0'));
        $terms = wp_get_post_terms($post->ID, 'mec_label', array('fields'=>'ids'));
    ?>
        <div class="mec-meta-box-labels-container">
            <div class="mec-form-row">
                <?php foreach($labels as $label): ?>
                <div class="mec-label-row">
                    <input <?php if(in_array($label->term_id, $terms)) echo 'checked="checked"'; ?> name="mec[labels][]" type="checkbox" value="<?php echo $label->term_id; ?>" id="mec_label<?php echo $label->term_id; ?>" />
                    <label for="mec_label<?php echo $label->term_id; ?>"><?php echo $label->name; ?></label>
                    <span class="mec-color" style="background-color: <?php echo get_term_meta($label->term_id, 'color', true); ?>"></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    }
    
    /**
     * Save label of event
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return void
     */
    public function save_event($post_id)
    {
        // Check if our nonce is set.
        if(!isset($_POST['mec_event_nonce'])) return;

        // Verify that the nonce is valid.
        if(!wp_verify_nonce($_POST['mec_event_nonce'], 'mec_event_data')) return;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return;

        // Get Modern Events Calendar Data
        $_mec = isset($_POST['mec']) ? $_POST['mec'] : array();
        
        $_labels = isset($_mec['labels']) ? (array) $_mec['labels'] : array();
        
        $_labels = array_map('intval', $_labels);
        $_labels = array_unique($_labels);
        
        wp_set_object_terms($post_id, $_labels, 'mec_label');
    }
}