<?php class WorkableAJAX {
    private $api;

    public function __construct() {
        $this->api = new WorkableAPI( [
            'subdomain'    => 'stirling-ackroyd-group',
            'access_token' => '1f821cb4260fa1629fc538bcde18203e951f712181cdcd41262f09ad55e12f2a'
        ] );

        add_action( 'wp_ajax_nopriv_jobs_list', [ $this, 'jobs_list' ] );
        add_action( 'wp_ajax_nopriv_job_detail', [ $this, 'job_detail' ] );
    }

    public function jobs_list() {
        $key = 'workable_jobs_query';

        $query = get_transient( $key );

        if ( $query === false ) {

            $query = $this->api->get( 'jobs', [
                'limit'          => 50,
                'state'          => 'published',
                'include_fields' => 'employment_type'
            ] );

            set_transient( $key, $query, HOUR_IN_SECONDS );
        }

        wp_send_json( $query->jobs );
    }

    public function job_detail() {
        $result    = [];
        $shortcode = $_POST['shortcode'];
        $context   = Timber::get_context();

        if ( ! empty( $shortcode ) ) {

            $key = 'workable_job_detail_' . $shortcode;

            $query = get_transient( $key );

            if ( $query === false ) {

                $query = $this->api->get( 'jobs/' . $shortcode );

                set_transient( $key, $query, DAY_IN_SECONDS );
            }

            $context['job']              = $query;
            $context['careers_page_url'] = get_the_permalink( get_page_id_by_template_name( 'page-careers' ) );

            $result['html']   = Timber::compile( 'components/sections/careers-job-detail.twig', $context );
            $result['status'] = true;
        } else {
            $result['status'] = false;
        }

        wp_send_json( $result );
    }

    public function _jobs_list() {
        $result = [];

        $query_args = [
            'limit'          => 50,
            'state'          => 'published',
            'include_fields' => 'employment_type'
        ];

        if ( isset( $_POST['since_id'] ) && ! empty( $_POST['since_id'] ) ) {
            $query_args['since_id'] = $_POST['since_id'];
        }

        $query           = get_transient( 'sa_jobs_list' );
        $result['trans'] = true;
        if ( $query === false ) {

            $query = $this->api->get( 'jobs', $query_args );

            set_transient( 'sa_jobs_list', $query, HOUR_IN_SECONDS );
            $result['trans'] = false;
        }


        if ( sizeof( $query->jobs ) > 0 ) {
            $careers_page_id = get_page_id_by_template_name( 'page-careers' );

            array_multisort( array_column( $query->jobs, 'created_at' ), SORT_DESC, $query->jobs );

            foreach ( $query->jobs as $job ) {
                $html .= Timber::compile( 'components/global/job-item.twig', [
                    'job'              => $job,
                    'careers_page_url' => get_the_permalink( $careers_page_id )
                ] );
            }
        }

        if ( isset( $query->paging ) ) {
            $parse_url = parse_url( $query->paging->next );
            parse_str( $parse_url['query'], $url_params );
            $result['since_id'] = $url_params['since_id'];
        }

        $result['html'] = $html;

        wp_send_json( $result );
    }
}

new WorkableAJAX();
