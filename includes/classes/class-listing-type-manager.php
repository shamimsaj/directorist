<?php

if (!class_exists('ATBDP_Listing_Type_Manager')) {
    class ATBDP_Listing_Type_Manager
    {
        public $fields = [];
        public $layouts = [];
        public $config = [];
        public $default_form = [];
        public $old_custom_fields = [];

        // run
        public function run()
        {
            $this->prepare_settings();
            $this->get_old_custom_fields();
            add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
            add_action('init', [$this, 'register_terms']);
            add_action('admin_menu', [$this, 'add_menu_pages']);
            add_action('admin_post_delete_listing_type', [$this, 'handle_delete_listing_type_request']);

            add_action('wp_ajax_save_post_type_data', [$this, 'save_post_type_data']);
        }

        // save_post_type_data
        public function save_post_type_data()
        {

            /*  wp_send_json( [
                'general_config' => $this->maybe_json( $_POST['general_config'] ),
                'status' => false,
                'status_log' => [
                    'name_is_missing' => [
                        'type' => 'error',
                        'message' => 'testing',
                    ],
                ],
            ], 200 ); */

            if (empty($_POST['name'])) {
                wp_send_json([
                    'status' => false,
                    'status_log' => [
                        'name_is_missing' => [
                            'type' => 'error',
                            'message' => 'Name is missing',
                        ],
                    ],
                ], 200);
            }

            $term_id = 0;
            $mode    = 'create';
            $listing_type_name = $_POST['name'];

            if (!empty($_POST['listing_type_id']) && absint($_POST['listing_type_id'])) {
                $mode = 'edit';
                $term_id = absint($_POST['listing_type_id']);
                wp_update_term($term_id, 'atbdp_listing_types', ['name' => $listing_type_name]);
            } else {
                $term = wp_insert_term($listing_type_name, 'atbdp_listing_types');

                if (is_wp_error($term)) {
                    if (!empty($term->errors['term_exists'])) {
                        wp_send_json([
                            'status' => false,
                            'status_log' => [
                                'name_exists' => [
                                    'type' => 'error',
                                    'message' => 'The name already exists',
                                ]
                            ],
                        ], 200);
                    }
                } else {
                    $mode = 'edit';
                    $term_id = $term['term_id'];
                }
            }

            if (empty($term_id)) {
                wp_send_json([
                    'status' => false,
                    'status_log' => [
                        'invalid_id' => [
                            'type' => 'error',
                            'message' => 'Error found, please try again',
                        ]
                    ],
                ], 200);
            }

            $created_message = ('create' == $mode) ? 'created' : 'updated';

            if (empty($_POST['field_list'])) {
                wp_send_json([
                    'status' => true,
                    'post_id' => $term_id,
                    'status_log' => [
                        'post_created' => [
                            'type' => 'success',
                            'message' => 'The Post type has been ' . $created_message . ' successfully',
                        ],
                        'field_list_not_found' => [
                            'type' => 'error',
                            'message' => 'Field list not found',
                        ],
                    ],
                ], 200);
            }
            $url = '';
            $field_list = $this->maybe_json($_POST['field_list']);
            foreach ($field_list as $field_key) {
                if (isset($_POST[$field_key]) && 'name' !==  $field_key) {
                    $this->update_validated_term_meta($term_id, $field_key, $_POST[$field_key]);
                }
            }
            $url = admin_url('edit.php?post_type=at_biz_dir&page=atbdp-listing-types&action=edit&listing_type_id=' . $term_id);
            wp_send_json([
                'status' => true,
                'post_id' => $term_id,
                'redirect_url' => $url,
                'status_log' => [
                    'post_created' => [
                        'type' => 'success',
                        'message' => 'The post type has been ' . $created_message . ' successfully'
                    ]
                ],
            ], 200);
        }

        // update_validated_term_meta
        public function update_validated_term_meta($term_id, $field_key, $value)
        {
            if (!isset($this->fields[$field_key]) && !array_key_exists($field_key, $this->config['fields_group'])) {
                return;
            }

            if ('toggle' === $this->fields[$field_key]['type']) {
                $value = ('true' === $value || true === $value || '1' === $value || 1 === $value) ? true : 0;
            }

            $value = $this->maybe_json($value);
            update_term_meta($term_id, $field_key, $value);
        }

        // maybe_json
        public function maybe_json($string)
        {
            $string_alt = $string;

            if (preg_match('/(\\\")/', $string_alt)) {
                $string_alt = preg_replace('/(\\\")/', '"', $string_alt);
                $string_alt = json_decode($string_alt, true);
                $string = (!is_null($string_alt)) ? $string_alt : $string;
            }

            return $string;
        }

        // maybe_serialize
        public function maybe_serialize($value = '')
        {
            return maybe_serialize($this->maybe_json($value));
        }

        public function get_old_custom_fields($fields_of = 'form')
        {
            $fields = [];
            $old_fields = atbdp_get_custom_field_ids('', true);
            foreach ($old_fields as $old_field) {
                $field_type = get_post_meta($old_field, 'type', true);
                $description = get_post_meta($old_field, 'instructions', true);
                $required = get_post_meta($old_field, 'required', true);
                $admin_use = get_post_meta($old_field, 'admin_use', true);
                $associate = get_post_meta($old_field, 'associate', true);
                $category_pass = get_post_meta($old_field, 'category_pass', true);
                $choices = get_post_meta($old_field, 'choices', true);
                $rows = get_post_meta($old_field, 'rows', true);
                $target = get_post_meta($old_field, 'target', true);
                $file_type = get_post_meta($old_field, 'file_type', true);
                $file_size = get_post_meta($old_field, 'file_size', true);
                if (('text' === $field_type) || ('number' === $field_type) || ('date' === $field_type) || ('color' === $field_type) || ('time' === $field_type)) {
                    $fields[$field_type] = [
                        'label' => get_the_title($old_field),
                        'icon' => 'fa fa-text-width',
                        'options' => [
                            'type' => [
                                'type'  => 'hidden',
                                'value' => 'text',
                            ],
                            'label' => [
                                'type'  => 'text',
                                'label' => 'Label',
                                'value' => get_the_title($old_field),
                            ],
                            'field_key' => [
                                'type'  => 'text',
                                'label' => 'Key',
                                'value' => $old_field,
                            ],
                            'placeholder' => [
                                'type'  => 'text',
                                'label' => 'Placeholder',
                                'value' => '',
                            ],
                            'description' => [
                                'type'  => 'text',
                                'label' => 'Description',
                                'value' => $description,
                            ],
                            'required' => [
                                'type'  => 'toggle',
                                'label'  => 'Required',
                                'value' => $required == 1 ? true : false,
                            ],
                            'only_for_admin' => [
                                'type'  => 'toggle',
                                'label'  => 'Only For Admin Use',
                                'value' =>  $admin_use == 1 ? true : false,
                            ],
                            'assign_to' => [
                                'type' => 'radio',
                                'label' => __('Assign to', 'directorist'),
                                'value' => $associate,
                                'options' => [
                                    'form'  => [
                                        'label' => __('Form', 'directorist'),
                                        'value' => 'form',
                                    ],
                                    'category'  => [
                                        'label' => __('Category', 'directorist'),
                                        'value' => 'category',
                                        'sub_options' => [
                                            'type' => 'select',
                                            'label' => __('Select Categories', 'directorist'),
                                            'value' => $category_pass,
                                            'options' => [
                                                [
                                                    'label' => 'Category A',
                                                    'value' => 'category_a'
                                                ],
                                                [
                                                    'label' => 'Category B',
                                                    'value' => 'category_b'
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ];
                }
                if (('radio' === $field_type) || ('checkbox' === $field_type) || ('select' === $field_type)) {
                    $fields[$field_type] = [
                        'label' => get_the_title($old_field),
                        'icon' => 'fa fa-text-width',
                        'options' => [
                            'type' => [
                                'type'  => 'hidden',
                                'value' => 'select',
                            ],
                            'label' => [
                                'type'  => 'text',
                                'label' => 'Label',
                                'value' => get_the_title($old_field),
                            ],
                            'field_key' => [
                                'type'  => 'text',
                                'label' => 'Key',
                                'value' => $old_field,
                            ],
                            'options' => [
                                'type' => 'textarea',
                                'label' => __('Options', 'directorist'),
                                'value' => $choices,
                                'description' => __('Each on a new line, for example,
                                Male: Male
                                Female: Female
                                Other: Other', 'directorist'),
                            ],
                            'description' => [
                                'type'  => 'text',
                                'label' => 'Description',
                                'value' => $description,
                            ],
                            'required' => [
                                'type'  => 'toggle',
                                'label'  => 'Required',
                                'value' => $required == 1 ? true : false,
                            ],
                            'only_for_admin' => [
                                'type'  => 'toggle',
                                'label'  => 'Only For Admin Use',
                                'value' =>  $admin_use == 1 ? true : false,
                            ],
                            'assign_to' => [
                                'type' => 'radio',
                                'label' => __('Assign to', 'directorist'),
                                'value' => $associate,
                                'options' => [
                                    'form'  => [
                                        'label' => __('Form', 'directorist'),
                                        'value' => 'form',
                                    ],
                                    'category'  => [
                                        'label' => __('Category', 'directorist'),
                                        'value' => 'category',
                                        'sub_options' => [
                                            'type' => 'select',
                                            'label' => __('Select Categories', 'directorist'),
                                            'value' => $category_pass,
                                            'options' => [
                                                [
                                                    'label' => 'Category A',
                                                    'value' => 'category_a'
                                                ],
                                                [
                                                    'label' => 'Category B',
                                                    'value' => 'category_b'
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ];
                }
                if (('textarea' === $field_type)) {
                    $fields[$field_type] = [
                        'label' => get_the_title($old_field),
                        'icon' => 'fa fa-text-width',
                        'options' => [
                            'type' => [
                                'type'  => 'hidden',
                                'value' => 'text',
                            ],
                            'label' => [
                                'type'  => 'text',
                                'label' => 'Label',
                                'value' => get_the_title($old_field),
                            ],
                            'field_key' => [
                                'type'  => 'text',
                                'label' => 'Key',
                                'value' => $old_field,
                            ],
                            'placeholder' => [
                                'type'  => 'text',
                                'label' => 'Placeholder',
                                'value' => '',
                            ],
                            'description' => [
                                'type'  => 'text',
                                'label' => 'Description',
                                'value' => $description,
                            ],
                            'rows' => [
                                'type'  => 'number',
                                'label' => $rows,
                                'value' => 8,
                            ],
                            'required' => [
                                'type'  => 'toggle',
                                'label'  => 'Required',
                                'value' => $required == 1 ? true : false,
                            ],
                            'only_for_admin' => [
                                'type'  => 'toggle',
                                'label'  => 'Only For Admin Use',
                                'value' =>  $admin_use == 1 ? true : false,
                            ],
                            'assign_to' => [
                                'type' => 'radio',
                                'label' => __('Assign to', 'directorist'),
                                'value' => $associate,
                                'options' => [
                                    'form'  => [
                                        'label' => __('Form', 'directorist'),
                                        'value' => 'form',
                                    ],
                                    'category'  => [
                                        'label' => __('Category', 'directorist'),
                                        'value' => 'category',
                                        'sub_options' => [
                                            'type' => 'select',
                                            'label' => __('Select Categories', 'directorist'),
                                            'value' => $category_pass,
                                            'options' => [
                                                [
                                                    'label' => 'Category A',
                                                    'value' => 'category_a'
                                                ],
                                                [
                                                    'label' => 'Category B',
                                                    'value' => 'category_b'
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ];
                }
                if (('url' === $field_type)) {
                    $fields[$field_type] = [
                        'label' => get_the_title($old_field),
                        'icon' => 'fa fa-text-width',
                        'options' => [
                            'type' => [
                                'type'  => 'hidden',
                                'value' => 'text',
                            ],
                            'label' => [
                                'type'  => 'text',
                                'label' => 'Label',
                                'value' => get_the_title($old_field),
                            ],
                            'field_key' => [
                                'type'  => 'text',
                                'label' => 'Key',
                                'value' => $old_field,
                            ],
                            'placeholder' => [
                                'type'  => 'text',
                                'label' => 'Placeholder',
                                'value' => '',
                            ],
                            'description' => [
                                'type'  => 'text',
                                'label' => 'Description',
                                'value' => $description,
                            ],
                            'required' => [
                                'type'  => 'toggle',
                                'label'  => 'Required',
                                'value' => $required == 1 ? true : false,
                            ],
                            'only_for_admin' => [
                                'type'  => 'toggle',
                                'label'  => 'Only For Admin Use',
                                'value' =>  $admin_use == 1 ? true : false,
                            ],
                            'target' => [
                                'type'  => 'toggle',
                                'label' => 'Open in new tab',
                                'value' => $target == '_blank' ? true : false,
                            ],
                            'assign_to' => [
                                'type' => 'radio',
                                'label' => __('Assign to', 'directorist'),
                                'value' => $associate,
                                'options' => [
                                    'form'  => [
                                        'label' => __('Form', 'directorist'),
                                        'value' => 'form',
                                    ],
                                    'category'  => [
                                        'label' => __('Category', 'directorist'),
                                        'value' => 'category',
                                        'sub_options' => [
                                            'type' => 'select',
                                            'label' => __('Select Categories', 'directorist'),
                                            'value' => $category_pass,
                                            'options' => [
                                                [
                                                    'label' => 'Category A',
                                                    'value' => 'category_a'
                                                ],
                                                [
                                                    'label' => 'Category B',
                                                    'value' => 'category_b'
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ];
                }
                if (('file' === $field_type)) {
                    $fields[$field_type] = [
                        'label' => get_the_title($old_field),
                        'icon' => 'fa fa-text-width',
                        'options' => [
                            'type' => [
                                'type'  => 'hidden',
                                'value' => 'text',
                            ],
                            'label' => [
                                'type'  => 'text',
                                'label' => 'Label',
                                'value' => get_the_title($old_field),
                            ],
                            'field_key' => [
                                'type'  => 'text',
                                'label' => 'Key',
                                'value' => $old_field,
                            ],
                            'placeholder' => [
                                'type'  => 'text',
                                'label' => 'Placeholder',
                                'value' => '',
                            ],
                            'description' => [
                                'type'  => 'text',
                                'label' => 'Description',
                                'value' => $description,
                            ],
                            'required' => [
                                'type'  => 'toggle',
                                'label'  => 'Required',
                                'value' => $required == 1 ? true : false,
                            ],
                            'only_for_admin' => [
                                'type'  => 'toggle',
                                'label'  => 'Only For Admin Use',
                                'value' =>  $admin_use == 1 ? true : false,
                            ],
                            'file_types' => [
                                'type'  => 'radio',
                                'label' => 'File Type',
                                'value' => $file_type,
                                'options' => [
                                    'all' => [
                                        'label' => __('All Types', 'directorist'),
                                        'value' => 'all',
                                    ],
                                    'image_format' => [
                                        [
                                            'label' => __('jpg', 'directorist'),
                                            'value' => 'jpg',
                                        ],
                                        [
                                            'label' => __('jpeg', 'directorist'),
                                            'value' => 'jpeg',
                                        ],
                                        [
                                            'label' => __('gif', 'directorist'),
                                            'value' => 'gif',
                                        ],
                                        [
                                            'label' => __('png', 'directorist'),
                                            'value' => 'png',
                                        ],
                                        [
                                            'label' => __('bmp', 'directorist'),
                                            'value' => 'bmp',
                                        ],
                                        [
                                            'label' => __('ico', 'directorist'),
                                            'value' => 'ico',
                                        ],
                                    ],
                                    'video_format' => [
                                        [
                                            'label' => __('asf', 'directorist'),
                                            'value' => 'asf',
                                        ],
                                        [
                                            'label' => __('flv', 'directorist'),
                                            'value' => 'flv',
                                        ],
                                        [
                                            'label' => __('avi', 'directorist'),
                                            'value' => 'avi',
                                        ],
                                        [
                                            'label' => __('mkv', 'directorist'),
                                            'value' => 'mkv',
                                        ],
                                        [
                                            'label' => __('mp4', 'directorist'),
                                            'value' => 'mp4',
                                        ],
                                        [
                                            'label' => __('mpeg', 'directorist'),
                                            'value' => 'mpeg',
                                        ],
                                        [
                                            'label' => __('mpg', 'directorist'),
                                            'value' => 'mpg',
                                        ],
                                        [
                                            'label' => __('wmv', 'directorist'),
                                            'value' => 'wmv',
                                        ],
                                        [
                                            'label' => __('3gp', 'directorist'),
                                            'value' => '3gp',
                                        ],
                                    ],
                                    'audio_format' => [
                                        [
                                            'label' => __('ogg', 'directorist'),
                                            'value' => 'ogg',
                                        ],
                                        [
                                            'label' => __('mp3', 'directorist'),
                                            'value' => 'mp3',
                                        ],
                                        [
                                            'label' => __('wav', 'directorist'),
                                            'value' => 'wav',
                                        ],
                                        [
                                            'label' => __('wma', 'directorist'),
                                            'value' => 'wma',
                                        ],
                                    ],
                                    'text_format' => [
                                        [
                                            'label' => __('css', 'directorist'),
                                            'value' => 'css',
                                        ],
                                        [
                                            'label' => __('csv', 'directorist'),
                                            'value' => 'csv',
                                        ],
                                        [
                                            'label' => __('htm', 'directorist'),
                                            'value' => 'htm',
                                        ],
                                        [
                                            'label' => __('html', 'directorist'),
                                            'value' => 'html',
                                        ],
                                        [
                                            'label' => __('txt', 'directorist'),
                                            'value' => 'txt',
                                        ],
                                        [
                                            'label' => __('rtx', 'directorist'),
                                            'value' => 'rtx',
                                        ],
                                        [
                                            'label' => __('vtt', 'directorist'),
                                            'value' => 'vtt',
                                        ],
                                    ],
                                    'application_format' => [
                                        [
                                            'label' => __('doc', 'directorist'),
                                            'value' => 'doc',
                                        ],
                                        [
                                            'label' => __('docx', 'directorist'),
                                            'value' => 'docx',
                                        ],
                                        [
                                            'label' => __('odt', 'directorist'),
                                            'value' => 'odt',
                                        ],
                                        [
                                            'label' => __('pdf', 'directorist'),
                                            'value' => 'pdf',
                                        ],
                                        [
                                            'label' => __('pot', 'directorist'),
                                            'value' => 'pot',
                                        ],
                                        [
                                            'label' => __('ppt', 'directorist'),
                                            'value' => 'ppt',
                                        ],
                                        [
                                            'label' => __('pptx', 'directorist'),
                                            'value' => 'pptx',
                                        ],
                                        [
                                            'label' => __('rar', 'directorist'),
                                            'value' => 'rar',
                                        ],
                                        [
                                            'label' => __('rtf', 'directorist'),
                                            'value' => 'rtf',
                                        ],
                                        [
                                            'label' => __('swf', 'directorist'),
                                            'value' => 'swf',
                                        ],
                                        [
                                            'label' => __('xls', 'directorist'),
                                            'value' => 'xls',
                                        ],
                                        [
                                            'label' => __('xlsx', 'directorist'),
                                            'value' => 'xlsx',
                                        ],
                                        [
                                            'label' => __('gpx', 'directorist'),
                                            'value' => 'gpx',
                                        ],
                                    ],

                                ],
                            ],
                            'file_size' => [
                                'type'  => 'text',
                                'label' => 'File Size',
                                'description' => __('Set maximum file size to upload', 'directorist'),
                                'value' => $file_size,
                            ],
                        ]
                    ];
                }
            }
            return $fields;
        }

        // prepare_settings
        public function prepare_settings()
        {
            $this->default_form = apply_filters('atbdp_default_listing_form_sections', [
                'general_information' => [
                    'label' => __('General Information', 'directorist'),
                    'fields' => apply_filters('atbdp_general_info_section_fields', ['title', 'description', 'pricing', $this->get_old_custom_fields(), 'location', 'tag', 'category', $this->get_old_custom_fields('category')]),
                ],

                'contact_information' => [
                    'label' => __('Contact Information', 'directorist'),
                    'fields' => apply_filters('atbdp_contact_info_section_fields', ['zip', 'phone', 'phone2', 'fax', 'email', 'website', 'social_info']),
                ],

                'map' => [
                    'label' => __('Map', 'directorist'),
                    'fields' => apply_filters('atbdp_map_section_fields', ['address', 'map']),
                ],

                'media' => [
                    'label' => __('Images & Video', 'directorist'),
                    'fields' => apply_filters('atbdp_media_section_fields', ['image_upload', 'video']),
                ],

                'submit_area' => [
                    'fields' => apply_filters('atbdp_submit_area_fields', ['terms_conditions', 'privacy_policy', 'submit_button']),
                ],

            ]);


            $form_field_widgets = [
                'preset' => [
                    'title' => 'Preset Fields',
                    'description' => 'Click on a field to use it',
                    'allow_multiple' => false,
                    'widgets' => [
                        'title' => [
                            'label' => 'Title',
                            'icon' => 'fa fa-text-height',
                            'lock' => true,
                            'show' => true,
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'listing_title',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Title',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => true,
                                ],

                            ],
                        ],

                        'description' => [
                            'label' => 'Description',
                            'icon' => 'fa fa-align-left',
                            'show' => true,
                            'options' => [
                                'type' => [
                                    'type'  => 'select',
                                    'label' => 'Type',
                                    'value' => 'wp_editor',
                                    'options' => [
                                        [
                                            'label' => __('Textarea', 'directorist'),
                                            'value' => 'textarea',
                                        ],
                                        [
                                            'label' => __('WP Editor', 'directorist'),
                                            'value' => 'wp_editor',
                                        ],
                                    ]
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'listing_content',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Description',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'name'  => 'required',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],

                                'plans' => [
                                    'type' => 'multi-fields',
                                    'label' => 'Setup the plan',
                                    'value' => [],
                                    'add-new-button-label' => 'Add new plan',
                                    'praimary_key' => 'plan_id',
                                    'show_if' => [[
                                        'conditions' => [
                                            ['key' => 'tag_with_plan', 'value' => true]
                                        ]
                                    ]],
                                    'options' => [
                                        'plan_id' => [
                                            'type' => 'select',
                                            'unique' => true,
                                            'label' => 'Chose a plan',
                                            'value' => '',
                                            'options-source' => [
                                                'where'      => 'package_list.options',
                                                'source_map' => [
                                                    'id'    => 'id',
                                                    'value' => 'value',
                                                    'label' => 'label',
                                                ],
                                                'filter_by' => 'package_list.value',
                                            ],
                                        ],
                                        'min' => [
                                            'type' => 'number',
                                            'label' => 'Min',
                                            'value' => '',
                                            'show_if' => [[
                                                'conditions' => [
                                                    ['key' => 'plan_id', 'value' => '', 'compare' => 'not']
                                                ]
                                            ]],
                                        ],
                                        'max' => [
                                            'type' => 'number',
                                            'label' => 'Max',
                                            'value' => '',
                                            'show_if' => [[
                                                'conditions' => [
                                                    ['key' => 'plan_id', 'value' => '', 'compare' => 'not']
                                                ]
                                            ]],
                                        ],
                                    ]
                                ]
                            ]
                        ],

                        'tagline' => [
                            'label' => 'Tagline',
                            'icon' => 'uil uil-text-fields',
                            'show' => true,
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'tagline',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Tagline',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'pricing' => [
                            'label' => 'Pricing',
                            'icon' => 'uil uil-bill',
                            'options' => [
                                'pricing_type' => [
                                    'type'  => 'select',
                                    'label'  => 'Select Pricing Type',
                                    'value' => 'both',
                                    'options' => [
                                        ['value' => '', 'label' => 'Select...'],
                                        ['value' => 'price_unit', 'label' => 'Price Unit'],
                                        ['value' => 'price_range', 'label' => 'Price Range'],
                                        ['value' => 'both', 'label' => 'Both'],
                                    ],
                                ],
                                'pricing_type' => [
                                    'type'  => 'select',
                                    'label'  => 'Select Pricing Type',
                                    'value' => 'both',
                                    'options' => [
                                        ['value' => '', 'label' => 'Select...'],
                                        ['value' => 'price_unit', 'label' => 'Price Unit'],
                                        ['value' => 'price_range', 'label' => 'Price Range'],
                                        ['value' => 'both', 'label' => 'Both'],
                                    ],
                                ],
                                'price_range_label' => [
                                    'type'  => 'text',
                                    'show_if' => [
                                        [
                                            'compare' => 'or',
                                            'conditions' => [
                                                ['key' => 'pricing_type', 'value' => 'both'],
                                                ['key' => 'pricing_type', 'value' => 'price_range'],
                                            ]
                                        ],

                                    ],
                                    'label'  => 'Price range label',
                                    'value' => 'Price range',
                                    'options' => [
                                        ['value' => 'number', 'label' => 'Number',],
                                        ['value' => 'text', 'label' => 'text',],
                                    ],
                                ],
                                'price_range_options' => [
                                    'type'  => 'select',
                                    'show_if'  => [
                                        [
                                            'compare' => 'or',
                                            'conditions' => [
                                                ['key' => 'pricing_type', 'value' => 'both'],
                                                ['key' => 'pricing_type', 'value' => 'price_range'],
                                            ]
                                        ],

                                    ],
                                    'label'  => 'Price Type Label',
                                    'value' => 'cheap',
                                    'options' => [
                                        ['value' => 'cheap', 'label' => 'Cheap',],
                                        ['value' => 'moderate', 'label' => 'Moderate',],
                                        ['value' => 'expensive', 'label' => 'Expensive',],
                                        ['value' => 'high', 'label' => 'Ultra High',],
                                    ],
                                ],
                                'price_unit_field_type' => [
                                    'type'  => 'select',
                                    'label'  => 'Price Unit field type',
                                    'show_if'  => [
                                        [
                                            'compare' => 'or',
                                            'conditions' => [
                                                ['key' => 'pricing_type', 'value' => 'both'],
                                                ['key' => 'pricing_type', 'value' => 'price_unit'],
                                            ]
                                        ],

                                    ],
                                    'value' => 'number',
                                    'options' => [
                                        ['value' => 'number', 'label' => 'Number',],
                                        ['value' => 'text', 'label' => 'Text',],
                                    ],
                                ],
                                'price_unit_field_label' => [
                                    'type'  => 'text',
                                    'label'  => 'Price Unit field label',
                                    'show_if'  => [
                                        [
                                            'compare' => 'or',
                                            'conditions' => [
                                                ['key' => 'pricing_type', 'value' => 'both'],
                                                ['key' => 'pricing_type', 'value' => 'price_unit'],
                                            ]
                                        ],
                                    ],
                                    'value' => 'Price [USD]',
                                    'options' => [
                                        ['value' => 'number', 'label' => 'Number',],
                                        ['value' => 'text', 'label' => 'text',],
                                    ],
                                ],
                            ]
                        ],

                        'view_count' => [
                            'label' => 'View Count',
                            'icon' => 'uil uil-eye',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'number',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'atbdp_post_views_count',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'View Count',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => true,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'excerpt' => [
                            'label' => 'Excerpt',
                            'icon' => 'uil uil-subject',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'textarea',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'excerpt',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Excerpt',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'location' => [
                            'label' => 'Location',
                            'icon' => 'uil uil-map-marker',
                            'options' => [
                                'type' => [
                                    'type'  => 'radio',
                                    'value' => 'multiple',
                                    'options' => [
                                        [
                                            'label' => __('Single Selection', 'directorist'),
                                            'value' => 'single',
                                        ],
                                        [
                                            'label' => __('Multi Selection', 'directorist'),
                                            'value' => 'multiple',
                                        ]
                                    ]
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'tax_input[at_biz_dir-location][]',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Location',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'tag' => [
                            'label' => 'Tag',
                            'icon' => 'uil uil-tag-alt',
                            'options' => [
                                'type' => [
                                    'type'  => 'radio',
                                    'value' => 'multiple',
                                    'options' => [
                                        [
                                            'label' => __('Single Selection', 'directorist'),
                                            'value' => 'single',
                                        ],
                                        [
                                            'label' => __('Multi Selection', 'directorist'),
                                            'value' => 'multiple',
                                        ],
                                    ]
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'tax_input[at_biz_dir-tags][]',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Tag',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'allow_new' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Allow New',
                                    'value' => true,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'category' => [
                            'label' => 'Category',
                            'icon' => 'uil uil-folder-open',
                            'options' => [
                                'type' => [
                                    'type'  => 'radio',
                                    'value' => 'multiple',
                                    'options' => [
                                        [
                                            'label' => __('Single Selection', 'directorist'),
                                            'value' => 'single',
                                        ],
                                        [
                                            'label' => __('Multi Selection', 'directorist'),
                                            'value' => 'multiple',
                                        ],
                                    ]
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'admin_category_select[]',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Category',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'address' => [
                            'label' => 'Address',
                            'icon' => 'uil uil-postcard',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'address',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Address',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'map' => [
                            'label' => 'Map',
                            'icon' => 'uil uil-map',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'map',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'map',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Map',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'zip' => [
                            'label' => 'Zip/Post Code',
                            'icon' => 'uil uil-map-pin',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'zip',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Zip/Post Code',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'phone' => [
                            'label' => 'Phone',
                            'icon' => 'uil uil-phone',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'tel',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'phone',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Phone',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'phone2' => [
                            'label' => 'Phone 2',
                            'icon' => 'uil uil-phone',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'tel',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'phone2',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Phone 2',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'fax' => [
                            'label' => 'Fax',
                            'icon' => 'uil uil-print',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'number',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'fax',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Fax',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'email' => [
                            'label' => 'Email',
                            'icon' => 'uil uil-envelope',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'email',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'email',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Email',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'website' => [
                            'label' => 'Website',
                            'icon' => 'uil uil-globe',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'website',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Website',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'social_info' => [
                            'label' => 'Social Info',
                            'icon' => 'uil uil-user-arrows',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'add_new',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'social',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Social Info',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'image_upload' => [
                            'label' => 'Images',
                            'icon' => 'uil uil-image',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'media',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'listing_img',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Select Files',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'max_image_limit' => [
                                    'type'  => 'number',
                                    'label' => 'Max Image Limit',
                                    'value' => '',
                                ],
                                'max_per_image_limit' => [
                                    'type'  => 'number',
                                    'label' => 'Max Upload Size Per Image in MB',
                                    'value' => '',
                                ],
                                'max_total_image_limit' => [
                                    'type'  => 'number',
                                    'label' => 'Total Upload Size in MB',
                                    'value' => '',
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'video' => [
                            'label' => 'Video',
                            'icon' => 'uil uil-video',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'videourl',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Video',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ],
                        ],

                        'terms_conditions' => [
                            'label' => 'Terms & Conditions',
                            'icon' => 'uil uil-file-exclamation-alt',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'checkbox',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 't_c_check',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'I agree with all',
                                ],
                                'linking_text' => [
                                    'type'  => 'text',
                                    'label' => 'Linking Text',
                                    'value' => 'Terms & Conditions',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => true,
                                ],
                            ],
                        ],

                        'privacy_policy' => [
                            'label' => 'Privacy & Policy',
                            'icon' => 'uil uil-file-exclamation-alt',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'checkbox',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'privacy_policy',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'I agree to the',
                                ],
                                'linking_text' => [
                                    'type'  => 'text',
                                    'label' => 'Linking Text',
                                    'value' => 'Privacy & Policy',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => true,
                                ],
                            ],
                        ],

                        'submit_button' => [
                            'label' => 'Submit Button',
                            'icon' => 'uil uil-link-h',
                            'lock' => true,
                            'show' => true,
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'button',
                                ],
                                'field_key' => [
                                    'type'  => 'hidden',
                                    'value' => 'submit_button',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Save & Preview',
                                ],
                                'preview' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Enable Preview Mode',
                                    'value' => true,
                                ]

                            ],
                        ],
                    ],
                ],

                'custom' => [
                    'title' => 'Custom Fields',
                    'description' => 'Click on a field type you want to create',
                    'allow_multiple' => true,
                    'widgets' => [
                        'text' => [
                            'label' => 'Text',
                            'icon' => 'uil uil-text',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Text',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'textarea' => [
                            'label' => 'Textarea',
                            'icon' => 'uil uil-text-fields',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'textarea',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'rows' => [
                                    'type'  => 'number',
                                    'label' => 'Rows',
                                    'value' => 8,
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'number' => [
                            'label' => 'Number',
                            'icon' => 'uil uil-0-plus',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'number',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'url' => [
                            'label' => 'URL',
                            'icon' => 'uil uil-link-add',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'text',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'target' => [
                                    'type'  => 'text',
                                    'label' => 'Open in new tab',
                                    'value' => '',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'date' => [
                            'label' => 'Date',
                            'icon' => 'uil uil-calender',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'date',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'time' => [
                            'label' => 'Time',
                            'icon' => 'uil uil-clock',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'time',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'placeholder' => [
                                    'type'  => 'text',
                                    'label' => 'Placeholder',
                                    'value' => '',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'color_picker' => [
                            'label' => 'Color',
                            'icon' => 'uil uil-palette',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'color_picker',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'select' => [
                            'label' => 'Select',
                            'icon' => 'uil uil-file-check',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'select',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'options' => [
                                    'type' => 'textarea',
                                    'label' => __('Options', 'directorist'),
                                    'description' => __('Each on a new line, for example,
                                    Male: Male
                                    Female: Female
                                    Other: Other', 'directorist'),
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'checkbox' => [
                            'label' => 'Checkbox',
                            'icon' => 'uil uil-check-square',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'checkbox',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'options' => [
                                    'type' => 'textarea',
                                    'label' => __('Options', 'directorist'),
                                    'description' => __('Each on a new line, for example,
                                    Male: Male
                                    Female: Female
                                    Other: Other', 'directorist'),
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'radio' => [
                            'label' => 'Radio',
                            'icon' => 'uil uil-circle',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'radio',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'options' => [
                                    'type' => 'custom-options',
                                    'label' => __('Options', 'directorist'),
                                    'description' => __('Each on a new line, for example,
                                        Male: Male
                                        Female: Female
                                        Other: Other', 'directorist'),
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'assign_to' => [
                                    'type' => 'radio',
                                    'label' => __('Assign to', 'directorist'),
                                    'value' => 'form',
                                    'options' => [
                                        [
                                            'label' => __('Form', 'directorist'),
                                            'value' => 'form',
                                        ],
                                        [
                                            'label' => __('Category', 'directorist'),
                                            'value' => 'category',
                                        ],
                                    ],
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],

                        'file' => [
                            'label' => 'File Upload',
                            'icon' => 'uil uil-file-upload-alt',
                            'options' => [
                                'type' => [
                                    'type'  => 'hidden',
                                    'value' => 'file',
                                ],
                                'label' => [
                                    'type'  => 'text',
                                    'label' => 'Label',
                                    'value' => 'Custom Field',
                                ],
                                'field_key' => [
                                    'type'  => 'text',
                                    'label' => 'Key',
                                    'value' => 'custom',
                                ],
                                'file_types' => [
                                    'type'  => 'select',
                                    'label' => 'Chose a file type',
                                    'value' => '',
                                    'options' => [
                                        [
                                            'label' => __('All Types', 'directorist'),
                                            'value' => 'all',
                                        ],
                                        [
                                            'group' => __('Image Format', 'directorist'),
                                            'options' => [
                                                [
                                                    'label' => __('jpg', 'directorist'),
                                                    'value' => 'jpg',
                                                ],
                                                [
                                                    'label' => __('jpeg', 'directorist'),
                                                    'value' => 'jpeg',
                                                ],
                                                [
                                                    'label' => __('gif', 'directorist'),
                                                    'value' => 'gif',
                                                ],
                                                [
                                                    'label' => __('png', 'directorist'),
                                                    'value' => 'png',
                                                ],
                                                [
                                                    'label' => __('bmp', 'directorist'),
                                                    'value' => 'bmp',
                                                ],
                                                [
                                                    'label' => __('ico', 'directorist'),
                                                    'value' => 'ico',
                                                ],
                                            ],
                                        ],
                                        [
                                            'group' => '',
                                            'options' => [
                                                [
                                                    'label' => __('asf', 'directorist'),
                                                    'value' => 'asf',
                                                ],
                                                [
                                                    'label' => __('flv', 'directorist'),
                                                    'value' => 'flv',
                                                ],
                                                [
                                                    'label' => __('avi', 'directorist'),
                                                    'value' => 'avi',
                                                ],
                                                [
                                                    'label' => __('mkv', 'directorist'),
                                                    'value' => 'mkv',
                                                ],
                                                [
                                                    'label' => __('mp4', 'directorist'),
                                                    'value' => 'mp4',
                                                ],
                                                [
                                                    'label' => __('mpeg', 'directorist'),
                                                    'value' => 'mpeg',
                                                ],
                                                [
                                                    'label' => __('mpg', 'directorist'),
                                                    'value' => 'mpg',
                                                ],
                                                [
                                                    'label' => __('wmv', 'directorist'),
                                                    'value' => 'wmv',
                                                ],
                                                [
                                                    'label' => __('3gp', 'directorist'),
                                                    'value' => '3gp',
                                                ],
                                            ],
                                        ],

                                        [
                                            'group' => '',
                                            'options' => [
                                                [
                                                    'label' => __('ogg', 'directorist'),
                                                    'value' => 'ogg',
                                                ],
                                                [
                                                    'label' => __('mp3', 'directorist'),
                                                    'value' => 'mp3',
                                                ],
                                                [
                                                    'label' => __('wav', 'directorist'),
                                                    'value' => 'wav',
                                                ],
                                                [
                                                    'label' => __('wma', 'directorist'),
                                                    'value' => 'wma',
                                                ],
                                            ],
                                        ],
                                        [
                                            'group' => 'Text Format',
                                            'options' => [
                                                [
                                                    'label' => __('css', 'directorist'),
                                                    'value' => 'css',
                                                ],
                                                [
                                                    'label' => __('csv', 'directorist'),
                                                    'value' => 'csv',
                                                ],
                                                [
                                                    'label' => __('htm', 'directorist'),
                                                    'value' => 'htm',
                                                ],
                                                [
                                                    'label' => __('html', 'directorist'),
                                                    'value' => 'html',
                                                ],
                                                [
                                                    'label' => __('txt', 'directorist'),
                                                    'value' => 'txt',
                                                ],
                                                [
                                                    'label' => __('rtx', 'directorist'),
                                                    'value' => 'rtx',
                                                ],
                                                [
                                                    'label' => __('vtt', 'directorist'),
                                                    'value' => 'vtt',
                                                ],
                                            ],

                                        ],
                                        [
                                            'group' => 'Application Format',
                                            'options' => [
                                                [
                                                    'label' => __('doc', 'directorist'),
                                                    'value' => 'doc',
                                                ],
                                                [
                                                    'label' => __('docx', 'directorist'),
                                                    'value' => 'docx',
                                                ],
                                                [
                                                    'label' => __('odt', 'directorist'),
                                                    'value' => 'odt',
                                                ],
                                                [
                                                    'label' => __('pdf', 'directorist'),
                                                    'value' => 'pdf',
                                                ],
                                                [
                                                    'label' => __('pot', 'directorist'),
                                                    'value' => 'pot',
                                                ],
                                                [
                                                    'label' => __('ppt', 'directorist'),
                                                    'value' => 'ppt',
                                                ],
                                                [
                                                    'label' => __('pptx', 'directorist'),
                                                    'value' => 'pptx',
                                                ],
                                                [
                                                    'label' => __('rar', 'directorist'),
                                                    'value' => 'rar',
                                                ],
                                                [
                                                    'label' => __('rtf', 'directorist'),
                                                    'value' => 'rtf',
                                                ],
                                                [
                                                    'label' => __('swf', 'directorist'),
                                                    'value' => 'swf',
                                                ],
                                                [
                                                    'label' => __('xls', 'directorist'),
                                                    'value' => 'xls',
                                                ],
                                                [
                                                    'label' => __('xlsx', 'directorist'),
                                                    'value' => 'xlsx',
                                                ],
                                                [
                                                    'label' => __('gpx', 'directorist'),
                                                    'value' => 'gpx',
                                                ],
                                            ],
                                        ]

                                    ],
                                ],
                                'file_size' => [
                                    'type'  => 'text',
                                    'label' => 'File Size',
                                    'description' => __('Set maximum file size to upload', 'directorist'),
                                    'value' => '2mb',
                                ],
                                'description' => [
                                    'type'  => 'text',
                                    'label' => 'Description',
                                    'value' => '',
                                ],
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                                'only_for_admin' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Only For Admin Use',
                                    'value' => false,
                                ],
                                'tag_with_plan' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Tag with plan',
                                    'value' => false,
                                ],
                                // 'plan' => [
                                //     'type'  => 'select',
                                //     'label'  => 'Chose a plan',
                                //     'show_if' => [
                                //         [
                                //             'key'     => 'tag_with_plan',
                                //             'compare' => '=',
                                //             'value'   => true,
                                //         ]
                                //     ],
                                //     'option_groups' => [
                                //         [
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'options' => [],
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //             'plan' => [
                                //                 'type'  => 'select',
                                //                 'label'  => 'Plan',
                                //                 'value' => '',
                                //             ],
                                //         ]

                                //         ],
                                // ],
                            ]

                        ],
                    ]



                ],
            ];

            $search_form_widgets = [
                'available_fields' => [
                    'title' => 'Available Fields',
                    'description' => 'Click on a field to use it',
                    'allow_multiple' => false,
                    'widgets' => [
                        'title' => [
                            'label' => 'Title',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => true,
                                ],
                            ],
                        ],

                        'description' => [
                            'label' => 'Description',
                            'icon' => 'fa fa-align-left',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]
                        ],

                        'tagline' => [
                            'label' => 'Tagline',
                            'icon' => 'uil uil-text-fields',
                            'show' => true,
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'pricing' => [
                            'label' => 'Pricing',
                            'icon' => 'fa fa-text-height',
                            'show' => true,
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'view_count' => [
                            'label' => 'View Count',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'excerpt' => [
                            'label' => 'Excerpt',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'location' => [
                            'label' => 'Location',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'tag' => [
                            'label' => 'Tag',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'category' => [
                            'label' => 'Category',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'address' => [
                            'label' => 'Address',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'map' => [
                            'label' => 'Map',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'zip' => [
                            'label' => 'Zip/Post Code',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'phone' => [
                            'label' => 'Phone',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'phone2' => [
                            'label' => 'Phone 2',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'fax' => [
                            'label' => 'Fax',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'email' => [
                            'label' => 'Email',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'website' => [
                            'label' => 'Website',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'social_info' => [
                            'label' => 'Social Info',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'image_upload' => [
                            'label' => 'Images',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'video' => [
                            'label' => 'Video',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'terms_conditions' => [
                            'label' => 'Terms & Conditions',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'privacy_policy' => [
                            'label' => 'Privacy & Policy',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ],
                        ],

                        'submit_button' => [
                            'label' => 'Submit Button',
                            'icon' => 'fa fa-text-height',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],

                            ],
                        ],

                        'text' => [
                            'label' => 'Text',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'textarea' => [
                            'label' => 'Textarea',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'number' => [
                            'label' => 'Number',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'url' => [
                            'label' => 'URL',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'date' => [
                            'label' => 'Date',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'time' => [
                            'label' => 'Time',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'color_picker' => [
                            'label' => 'Color',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'select' => [
                            'label' => 'Select',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'checkbox' => [
                            'label' => 'Checkbox',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'radio' => [
                            'label' => 'Radio',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],

                        'file' => [
                            'label' => 'File Upload',
                            'icon' => 'fa fa-text-width',
                            'options' => [
                                'required' => [
                                    'type'  => 'toggle',
                                    'label'  => 'Required',
                                    'value' => false,
                                ],
                            ]

                        ],
                    ],
                ],
            ];

            $this->fields = [
                'name' => [
                    'label' => 'Name *',
                    'type'  => 'text',
                    'value' => '',
                    'rules' => [
                        'required' => true,
                    ],
                ],

                'icon' => [
                    'label' => 'Icon',
                    'type'  => 'icon',
                    'value' => '',
                    'rules' => [
                        'required' => false,
                    ],
                ],

                'singular_name' => [
                    'label' => 'Singular name (e.g. Business)',
                    'type'  => 'text',
                    'value' => '',
                    'rules' => [
                        'required' => false,
                    ],
                ],

                'plural_name' => [
                    'label' => 'Plural name (e.g. Businesses)',
                    'type'  => 'text',
                    'value' => '',
                    'rules' => [
                        'required' => false,
                    ],
                ],

                'permalink' => [
                    'label' => 'Permalink',
                    'type'  => 'text',
                    'value' => '',
                    'rules' => [
                        'required' => false,
                    ],
                ],

                'preview_image' => [
                    'label'       => __('Select', 'directorist'),
                    'type'        => 'wp-media-picker',
                    'default-img' => ATBDP_PUBLIC_ASSETS . 'images/grid.jpg',
                    'value'       => '',
                ],

                'enable_package' => [
                    'label' => __('Enable paid listing packages', 'directorist'),
                    'type'  => 'toggle',
                    'name'  => 'enable_package',
                    'value' => '',
                ],
                'package_list' => [
                    'type' => 'checkbox',
                    'name' => 'package_list',
                    'show_if' => [
                        ['conditions' => [['key' => 'enable_package', 'value' => true]]],
                    ],
                    'label' => __('Select Packages', 'directorist'),
                    'value' => [],
                    'options' => [
                        ['value' => '1', 'id' => 'plan-1', 'label' => 'Plan A'],
                        ['value' => '2', 'id' => 'plan-2', 'label' => 'Plan B'],
                        ['value' => '3', 'id' => 'plan-3', 'label' => 'Plan C'],
                    ]
                ],
                'export' => [
                    'label' => __('Export config file', 'directorist'),
                    'type'  => 'button',
                    'link' => '',
                    'id'    => 'listing-type-export',
                    'extra_class' => 'cptm-btn cptm-btn-secondery',
                    'target' => '',
                    'icon'  => 'fa fa-download',
                ],
                'import' => [
                    'label' => __('Import config file', 'directorist'),
                    'type'  => 'button',
                    'link' => '',
                    'id'    => 'listing-type-import',
                    'extra_class' => 'cptm-btn cptm-btn-primary',
                    'target' => '',
                    'icon'  => 'fa fa-upload',
                ],
                'default_expiration' => [
                    'label' => __('Default expiration in days', 'directorist'),
                    'type'  => 'number',
                    'value' => '',
                    'placeholder' => '365',
                    'rules' => [
                        'required' => true,
                    ],
                ],

                'new_listing_status' => [
                    'label' => __('New Listing Default Status', 'directorist'),
                    'type'  => 'select',
                    'value' => '',
                    'options' => [
                        [
                            'label' => 'Select...',
                            'value' => '',
                        ],
                        [
                            'label' => __('Pending', 'directorist'),
                            'value' => 'pending',
                        ],
                        [
                            'label' => __('Publish', 'directorist'),
                            'value' => 'publish',
                        ],
                    ],
                ],

                'edit_listing_status' => [
                    'label' => __('Edited Listing Default Status', 'directorist'),
                    'type'  => 'select',
                    'value' => '',
                    'options' => [
                        [
                            'label' => 'Select...',
                            'value' => '',
                        ],
                        [
                            'label' => __('Pending', 'directorist'),
                            'value' => 'pending',
                        ],
                        [
                            'label' => __('Publish', 'directorist'),
                            'value' => 'publish',
                        ],
                    ],
                ],

                'global_listing_type' => [
                    'label' => __('Global Listing Type', 'directorist'),
                    'type'  => 'toggle',
                    'value' => '',
                ],

                'submission_form_fields' => [
                    'type'    => 'form-builder',
                    'widgets' => $form_field_widgets,
                    'group-options' => [
                        'label' => [
                            'type'  => 'text',
                            'label' => 'Group Name',
                            'value' => '',
                        ],

                        'tag_with_plan' => [
                            'type'  => 'toggle',
                            'label'  => 'Tag with plan',
                            'value' => false,
                        ],

                        'plans' => [
                            'type' => 'checkbox',
                            'label' => 'Chose the plans',
                            'name' => 'submission_form_fields_group_plans',
                            'value' => [],
                            'show_if' => [[
                                'conditions' => [
                                    ['key' => 'tag_with_plan', 'value' => true]
                                ]
                            ]],
                            'options' => [
                                ['id' => 'a', 'value' => 'a', 'label' => 'A'],
                                ['id' => 'b', 'value' => 'b', 'label' => 'B'],
                                ['id' => 'c', 'value' => 'c', 'label' => 'C'],
                            ],
                            'options-source' => [
                                'where'      => 'package_list.options',
                                'filter_by'  => 'package_list.value',
                                'value_from' => 'value',
                                'label_from' => 'label'
                            ],
                        ]
                    ],

                    'value' => [
                        'fields' => [
                            'title' => [
                                'widget_group' => 'preset',
                                'widget_name' => 'title',
                                'type'        => 'text',
                                'field_key'   => 'listing_title',
                                'required'    => true,
                                'label'       => 'Title',
                                'placeholder' => '',
                                'tag_with_plan' => true,
                                'plans' => [
                                    ['plan_id' => 1, 'max_length' => 200]
                                ],
                            ],
                        ],
                        'groups' => [
                            [
                                'label' => 'General Group',
                                'lock' => true,
                                'fields' => ['title'],
                            ],
                        ]
                    ],

                ],

                'search_form_fields' => [
                    'type'    => 'form-builder',
                    'widgets' => $search_form_widgets,
                    'dependency' => 'submission_form_fields',
                    'allow_add_new_section' => false,
                    'value'   => [
                        'groups' => [
                            [
                                'label' => 'Basic',
                                'fields' => [],
                            ],
                            [
                                'label' => 'Advanced',
                                'fields' => [],
                            ],
                        ]
                    ],
                ],

                'listings_card_grid_view' => [
                    'type' => 'card-builder',
                    'view' => 'grid-view',
                    'widgets' => [
                        'listing_title' => [
                            'type' => "title",
                            'id' => "listing_title",
                            'label' => "Listing Title",
                            'icon' => '<span class="uil uil-text-fields"></span>',
                            'options' => [
                              'title' => "Listing Title Settings",
                              'fields' => [
                                'label' => [
                                  'type' => "text",
                                  'label' => "Label",
                                  'value' => "text",
                                ],
                              ],
                            ],
                          ],
                  
                          'open_now_badge' => [
                            'type' => "badge",
                            'id' => "open_now_badge",
                            'label' => "Open Now",
                            'icon' => '<span class="uil uil-text-fields"></span>',
                            'options' => [
                              'title' => "Open/Closed Settings",
                              'fields' => [
                                'label' => [
                                  'type' => "text",
                                  'label' => "Label",
                                  'value' => "Open Now",
                                ],
                              ],
                            ],
                          ],
                  
                          'favorite_badge' => [
                            'type' => "badge",
                            'id' => "favorite_badge",
                            'label' => "Favorite",
                            'icon' => '<span class="uil uil-text-fields"></span>',
                            'options' => [
                              'title' => "Favorite Settings",
                              'fields' => [
                                'icon' => [
                                  'type' => "icon",
                                  'label' => "Icon",
                                  'value' => "fa fa-heart",
                                ],
                              ],
                            ],
                          ],
                  
                          'category' => [
                            'type' => "badge",
                            'id' => "category",
                            'label' => "Category",
                            'icon' => '<span class="uil uil-text-fields"></span>',
                            'options' => [
                              'title' => "Category Settings",
                              'fields' => [
                                'icon' => [
                                  'type' => "icon",
                                  'label' => "Icon",
                                  'value' => "fa fa-folder",
                                ],
                              ],
                            ],
                          ],
                  
                          'user_avater' => [
                            'type' => "avater",
                            'id' => "user_avater",
                            'label' => "User Avater",
                            'icon' => '<span class="uil uil-text-fields"></span>',
                            'options' => [
                              'title' => "Category Settings",
                              'fields' => [
                                'align' => [
                                  'type' => "select",
                                  'label' => "Align",
                                  'value' => "center",
                                ],
                              ],
                            ],
                          ],
                    ],
                    
                    'layout' => [
                        'thumbnail' => [
                            'top_right' => [
                                'label' => 'Top Right',
                                'maxWidget' => 2,
                                'maxWidgetInfoText' => "Up to __DATA__ item{s} can be added",
                                'acceptedWidgets' => ["open_now_badge", "favorite_badge"],
                            ],
                            'top_left' => [
                                'maxWidget' => 2,
                                'acceptedWidgets' => ["open_now_badge", "favorite_badge"],
                            ],
                            'bottom_right' => [
                                'maxWidget' => 2,
                                'acceptedWidgets' => ["open_now_badge", "favorite_badge"],
                            ],
                            'bottom_left' => [
                                'maxWidget' => 2,
                                'acceptedWidgets' => ["open_now_badge", "favorite_badge"],
                            ],
                            'avater' => [
                                'maxWidget' => 1,
                                'acceptedWidgets' => ["user_avater"],
                            ],
                        ],

                        'middle' => [
                            'body' => [
                                'maxWidget' => 2,
                                'widgetGroups' => [
                                    ['label' => 'Preset', 'widgets' => ['listing_title']],
                                    ['label' => 'Custom', 'widgets' => ['listing_title']],
                                ],
                                'acceptedWidgets' => ["listing_title"],
                            ],
                        ],

                        'footer' => [
                            'right' => [
                                'maxWidget' => 2,
                                'acceptedWidgets' => ["category"],
                            ],

                            'left' => [
                                'maxWidget' => 2,
                                'acceptedWidgets' => ["category"],
                            ],
                        ],
                    ],

                    'value' => '',
                ],

                'listings_card_list_view' => [
                    'type' => 'text',
                    'view' => 'list_view',
                    'value' => '',
                ],

                'listings_card_height' => [
                    'type' => 'number',
                    'label' => 'Height',
                    'value' => '250',
                    'unit' => 'px',
                    'units' => [
                        ['label' => 'px', 'value' => 'px'],
                        ['label' => '%', 'value' => '%'],
                    ],
                ],

                'listings_card_width' => [
                    'type' => 'number',
                    'label' => 'Width',
                    'value' => '100',
                    'unit' => '%',
                    'units' => [
                        ['label' => 'px', 'value' => 'px'],
                        ['label' => '%', 'value' => '%'],
                    ],
                ],

            ];


            $this->layouts = apply_filters('atbdp_listing_type_settings', [
                'general' => [
                    'label' => 'General',
                    'icon' => '<i class="uil uil-estate"></i>',
                    'submenu' => apply_filters('atbdp_listing_type_general_submenu', [
                        'general' => [
                            'label' => __('General', 'directorist'),
                            'sections' => [
                                'labels' => [
                                    'title'       => __('Labels', 'directorist'),
                                    'description' => '',
                                    'fields'      => [
                                        'name',
                                        'icon',
                                        'singular_name',
                                        'plural_name',
                                        'permalink',
                                    ],
                                ],
                            ],
                        ],
                        'preview_image' => [
                            'label' => __('Preview Image', 'directorist'),
                            'sections' => [
                                'labels' => [
                                    'title'       => __('Default Preview Image', 'directorist'),
                                    'description' => __('This image will be used when listing preview image is not present. Leave empty to hide the preview image completely.', 'directorist'),
                                    'fields'      => [
                                        'preview_image',
                                    ],
                                ],
                            ],
                        ],
                        'packages' => [
                            'label' => 'Packages',
                            'sections' => [
                                'labels' => [
                                    'title'       => 'Paid listing packages',
                                    'description' => 'Set what packages the user can choose from when submitting a listing of this type.',
                                    'fields'      => [
                                        'enable_package',
                                        'package_list',
                                    ],
                                ],
                            ],
                        ],
                        'other' => [
                            'label' => __('Other', 'directorist'),
                            'sections' => [
                                'labels' => [
                                    [
                                        'title'       => __('Default Status', 'directorist'),
                                        'description' => __('Need help?', 'directorist'),
                                        'fields'      => [
                                            'new_listing_status',
                                            'edit_listing_status',
                                        ],
                                    ],
                                    [
                                        'title'       => __('Expiration', 'directorist'),
                                        'description' => __('Default time to expire a listing.', 'directorist'),
                                        'fields'      => [
                                            'default_expiration',
                                        ],
                                    ],
                                    [
                                        'title'       => __('Export & Import Config File', 'directorist'),
                                        'description' => __('Bulk import and export all the form, layout and settings', 'directorist'),
                                        'fields'      => [
                                            'export',
                                            'import',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ],

                'submission_form' => [
                    'label' => 'Submission Form',
                    'icon' => '<span class="uil uil-file-edit-alt"></span>',
                    'sections' => [
                        'form_fields' => [
                            'title' => __('Select or create fields for this listing type', 'directorist'),
                            'description' => 'need help?',
                            'fields' => [
                                'submission_form_fields'
                            ],
                        ],
                    ],

                ],

                'single_page_layout' => [
                    'label' => 'Single Page Layout',
                    'icon' => '<span class="uil uil-credit-card"></span>',
                ],
                'listings_card_layout' => [
                    'label' => 'Listings Card Layout',
                    'icon' => '<span class="uil uil-list-ul"></span>',
                    'submenu' => [
                        'grid_view' => [
                            'label' => 'Listings Card Grid Layout',
                            'sections' => [
                                'listings_card' => [
                                    'title' => __('Create and customize the listing card for grid view', 'directorist'),
                                    'description' => 'need help? Read the documentation or open a ticket in our helpdesk.',
                                    'fields' => [
                                        'listings_card_grid_view'
                                    ],
                                ],
                            ],
                        ],
                        'list_view' => [
                            'label' => 'Listings Card List Layout',
                            'sections' => [
                                'listings_card' => [
                                    'title' => __('Create and customize the listing card for listing view', 'directorist'),
                                    'description' => 'need help?',
                                    'fields' => [
                                        'listings_card_list_view'
                                    ],
                                ],
                            ],
                        ],
                        'options' => [
                            'label' => 'Listings Card Options',
                            'sections' => [
                                'listings_card_options' => [
                                    'title' => __('Customize the options', 'directorist'),
                                    'description' => 'need help?',
                                    'fields' => [
                                        'listings_card_height',
                                        'listings_card_width'
                                    ],
                                ],
                            ],
                        ],
                    ],

                ],
                'search_forms' => [
                    'label' => 'Search Forms',
                    'icon' => '<span class="uil uil-search"></span>',
                    'sections' => [
                        'form_fields' => [
                            'title' => __('Customize the search form for this listing type', 'directorist'),
                            'description' => 'need help?',
                            'fields' => [
                                'search_form_fields'
                            ],
                        ],
                    ],
                ],
            ]);

            $this->config = [
                'fields_group' => [
                    'general_config' => [
                        'icon',
                        'singular_name',
                        'plural_name',
                        'permalink',
                        'preview_image',
                        'archive_general' => [
                            'listings_card_height',
                            'listings_card_width',
                        ]
                    ]
                ]
            ];
        }

        // add_menu_pages
        public function add_menu_pages()
        {
            add_submenu_page(
                'edit.php?post_type=at_biz_dir',
                'Listing Types',
                'Listing Types',
                'manage_options',
                'atbdp-listing-types',
                [$this, 'menu_page_callback__listing_types'],
                5
            );
        }

        // menu_page_callback__listing_types
        public function menu_page_callback__listing_types()
        {
            $post_types_list_table = new Listing_Types_List_Table($this);

            $action = $post_types_list_table->current_action();
            $post_types_list_table->prepare_items();

            $listing_type_id = 0;

            if (!empty($action) && ('edit' === $action) && !empty($_REQUEST['listing_type_id'])) {
                $listing_type_id = $_REQUEST['listing_type_id'];
                $this->update_fields_with_old_data();
            }

            $data = [
                'post_types_list_table' => $post_types_list_table,
                'fields'                => json_encode($this->fields),
                'layouts'               => json_encode($this->layouts),
                'config'                => json_encode($this->config),
                'id'                    => $listing_type_id,
                'add_new_link'          => admin_url('edit.php?post_type=at_biz_dir&page=atbdp-listing-types&action=add_new'),
            ];

            if (!empty($action) && ('edit' === $action || 'add_new' === $action)) {
                $this->enqueue_scripts();
                atbdp_load_admin_template('post-types-manager/edit-listing-type', $data);

                return;
            }

            atbdp_load_admin_template('post-types-manager/all-listing-types', $data);
        }

        // update_fields_with_old_data
        public function update_fields_with_old_data()
        {
            $listing_type_id = absint($_REQUEST['listing_type_id']);
            $term = get_term($listing_type_id, 'atbdp_listing_types');

            if (!$term) {
                return;
            }

            $this->fields['name']['value'] = $term->name;

            $all_term_meta = get_term_meta($term->term_id);
            if ('array' !== getType($all_term_meta)) {
                return;
            }

            foreach ($all_term_meta as $meta_key => $meta_value) {
                if (isset($this->fields[$meta_key])) {
                    $value = maybe_unserialize(maybe_unserialize($meta_value[0]));
                    $this->fields[$meta_key]['value'] = $value;
                }
            }

            foreach ($this->config['fields_group'] as $group_key => $group_fields) {
                if (array_key_exists($group_key, $all_term_meta)) {
                    $group_value = maybe_unserialize(maybe_unserialize($all_term_meta[$group_key][0]));

                    foreach ($group_fields as $field_index => $field_key) {
                        if ('string' === gettype($field_key) && array_key_exists($field_key, $this->fields)) {
                            $this->fields[$field_key]['value'] = $group_value[$field_key];
                        }

                        if ('array' === gettype($field_key)) {
                            foreach ($field_key as $sub_field_key) {
                                if (array_key_exists($sub_field_key, $this->fields)) {
                                    $this->fields[$sub_field_key]['value'] = $group_value[$field_index][$sub_field_key];
                                }
                            }
                        }
                    }
                }
            }

            // $test = get_term_meta( $listing_type_id, 'submission_form_fields' );
            // var_dump( $test );
        }

        // handle_delete_listing_type_request
        public function handle_delete_listing_type_request()
        {

            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'delete_listing_type')) {
                wp_die('Are you cheating? | _wpnonce');
            }

            if (!current_user_can('manage_options')) {
                wp_die('Are you cheating? | manage_options');
            }

            $term_id = isset($_REQUEST['listing_type_id']) ? absint($_REQUEST['listing_type_id']) : 0;

            $this->delete_listing_type($term_id);


            wp_redirect(admin_url('edit.php?post_type=at_biz_dir&page=atbdp-listing-types'));
            exit;
        }

        // delete_listing_type
        public function delete_listing_type($term_id = 0)
        {
            if (wp_delete_term($term_id, 'atbdp_listing_types')) {
                atbdp_add_flush_alert([
                    'id'      => 'deleting_listing_type_status',
                    'page'    => 'all-listing-type',
                    'message' => 'Successfully Deleted the listing type',
                ]);
            } else {
                atbdp_add_flush_alert([
                    'id'      => 'deleting_listing_type_status',
                    'page'    => 'all-listing-type',
                    'type'    => 'error',
                    'message' => 'Failed to delete the listing type'
                ]);
            }
        }

        // register_terms
        public function register_terms()
        {
            register_taxonomy('atbdp_listing_types', [ATBDP_POST_TYPE], [
                'hierarchical' => false,
                'labels' => [
                    'name' => _x('Listing Type', 'taxonomy general name', 'directorist'),
                    'singular_name' => _x('Listing Type', 'taxonomy singular name', 'directorist'),
                    'search_items' => __('Search Listing Type', 'directorist'),
                    'menu_name' => __('Listing Type', 'directorist'),
                ],
                'show_ui' => false,
            ]);
        }

        // enqueue_scripts
        public function enqueue_scripts()
        {
            wp_enqueue_media();
            wp_enqueue_style('atbdp-unicons');
            wp_enqueue_style('atbdp-font-awesome');
            wp_enqueue_style('atbdp_admin_css');

            wp_localize_script('atbdp_admin_app', 'ajax_data', ['ajax_url' => admin_url('admin-ajax.php')]);
            wp_enqueue_script('atbdp_admin_app');
        }

        // register_scripts
        public function register_scripts()
        {
            wp_register_style('atbdp-unicons', '//unicons.iconscout.com/release/v3.0.3/css/line.css', false);
            wp_register_style('atbdp-font-awesome', ATBDP_PUBLIC_ASSETS . 'css/font-awesome.min.css', false);
        }
    }
}