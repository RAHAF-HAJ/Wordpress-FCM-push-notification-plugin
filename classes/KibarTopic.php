<?php

if (!defined('ABSPATH'))
    exit;
/*
 * Create Topic taxonomy class
 * */

if(!class_exists('KibarTopic')):
    class KibarTopic
    {
        /**
         * KibarTopic constructor.
         * if Notification supports topic
         * 1- add a custom taxonomy with a topic name
         * 2- add initial term to it
         */
        function __construct()
        {
            if(KibarNotification::$hasTopic) {
                //register a new taxonomy with topic
                add_action('init', [$this, 'createTopicTaxonomy']);
                //Add topic column to notification table if it doesn't exist
                global $wpdb;
                $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '" . $wpdb->prefix . KibarNotification::$NOTIFICATION_TABLE_NAME . "' AND column_name = 'topic'");

                if (empty($row)) {
                    $wpdb->query("ALTER TABLE " . $wpdb->prefix . KibarNotification::$NOTIFICATION_TABLE_NAME . " ADD topic varchar(150) DEFAULT NULL");
                }
            }
        }

        /**
         * Create a custom taxonomy
         */
        function createTopicTaxonomy() {
            $args = array(
                'hierarchical'                      => true,
                'labels' => array(
                    'name'                          => _x('Topic', 'taxonomy general name' ),
                    'singular_name'                 => _x('Topic', 'taxonomy singular name'),
                    'search_items'                  => __('Search Topic'),
                    'popular_items'                 => __('Popular Topic'),
                    'all_items'                     => __('All Topic'),
                    'edit_item'                     => __('Edit Topic'),
                    'edit_item'                     => __('Edit Topic'),
                    'update_item'                   => __('Update Topic'),
                    'add_new_item'                  => __('Add New Topic'),
                    'new_item_name'                 => __('New Topic Name'),
                    'separate_items_with_commas'    => __('Seperate Topic with Commas'),
                    'add_or_remove_items'           => __('Add or Remove Topic'),
                    'choose_from_most_used'         => __('Choose from Most Used Topic')
                ),
                'query_var'                         => true,
                'rewrite'                           => array('slug' =>'topic')
            );
            register_taxonomy( 'topic', array( 'post' ), $args );
            
            $this->createInitTerms();
        }

        /**
         * Insert initial terms.
         */
        function createInitTerms() {
            $this->taxonomy = 'topic';
            $term = array (
                    'name'          => 'All',
                    'slug'          => 'all',
                    'description'   => 'This term is for all topics',
                );
            $this->setNewTerm($term);
            $term = array (
                'name'          => 'Test',
                'slug'          => 'test',
                'description'   => 'This term is for all topics',
            );
            $this->setNewTerm($term);
        }

        function setNewTerm($term) {
            wp_insert_term(
                $term['name'],
                $this->taxonomy,
                array(
                    'description'   => $term['description'],
                    'slug'          => $term['slug'],
                )
            );
            unset( $term );
        }
        /**
         * Get all the taxonomy terms as select list
         */
        public static function getTopicTerms($term_id = '') {
            $html = '<select style="width: 170px" name="topic">';
            $terms = get_terms([
                'taxonomy' => 'topic',
                'hide_empty' => false,
            ]);
            $first_term = $terms[0];
            $selected = '';
            $selected = (!empty($term_id) && $first_term->term_id == $term_id) ? 'selected' : self::isSelectedTerms($first_term->name);
            $html .= '<option '. $selected .' value="'. $first_term->term_id .'">' . $first_term->name . '</option>';
            if(count($terms) > 1) {
                foreach ($terms as $ind => $term) {
                    if($ind > 0) {
                        $selected = (!empty($term_id) && $term->term_id == $term_id) ? 'selected' : self::isSelectedTerms($term->name);
                        $html .= '<option ' . $selected . ' value="' . $term->term_id . '">' . $term->name . '</option>';
                    }
                }
            }
            $html .= '</select>';
            return $html;
        }

        static function isSelectedTerms($name) {
            if($name == 'All') {
                return 'selected';
            }
            return '';
        }
    }
    endif;
new KibarTopic();