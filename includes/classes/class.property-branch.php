<?php class SA_PropertyBranch {
    private $property;
    private $department;
    private $meta_department;

    public function __construct($property) {
        $this->property = $property;
        $this->department = $property->_department;
        $this->meta_department = ($this->department == 'residential-sales') ? 'sale' : 'let';
    }

    public function get_code() {
        $property_ref_id = $this->property->get_imported_id();
        $property_ref_id_parts = array_reverse(explode('-',$property_ref_id));
        $property_ref_office_part = reset($property_ref_id_parts);

        return substr($property_ref_office_part,0,3);
    }

    public function get_branch_post() {
        $branch_data = [
            'post_type' => 'sa_branch',
            'post_status' => 'publish',
            'nopaging' => true,
            'meta_query' => []
        ];

        $branch_data['meta_query'] = [
            [
                'key' => "branch_{$this->meta_department}_code",
                'value' => $this->get_code()
            ]
        ];

        $branches_query = new WP_Query($branch_data);

        if($branches_query->have_posts()) {
            return $branches_query->post;
        }

        return false;
    }

    public function get_branch_id() {
        $branch_post = $this->get_branch_post();

        if($branch_post) {
            return $branch_post->ID;
        }

        return false;
    }

    public function get_phone() {
        return get_post_meta($this->get_branch_id(), "branch_{$this->meta_department}_phone", true);
    }

    public function get_address() {
        return get_post_meta($this->get_branch_id(), "branch_address", true);
    }

    public function get_data() {
        $data = [];

        if($this->get_branch_id()) {
            $data = [
                'id' => $this->get_branch_id(),
                'department' => $this->department,
                'code' => $this->get_code(),
                'title' => get_the_title($this->get_branch_id()),
                'address' => $this->get_address(),
                'phone' => $this->get_phone()
            ];
        }

        return $data;
    }

    public function get_insights($count = 3) {
        return new Timber\PostQuery([
            'post_type' => 'post',
            'posts_per_page' => $count,
            'meta_query' => [
                [
                    'key' => 'post_branch_id',
                    'value' => $this->get_branch_id()
                ]
            ]
        ]);
    }
}
