<?php class WorkableAPI {
    public $subdomain = '';
    public $access_token = '';
    public $api_url;
    public $headers = [];

    public function __construct( $config ) {
        $this->subdomain    = $config['subdomain'];
        $this->access_token = $config['access_token'];
        $this->api_url      = 'https://' . $this->subdomain . '.workable.com/spi/v3/';
        $this->headers      = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token
            ]
        ];
    }

    public function get( $name, $args = [] ) {
        $request_data = http_build_query( $args );

        $request = wp_remote_get( $this->api_url . '/' . $name . '?' . $request_data, $this->headers );
        if ( ! is_wp_error( $request ) && $request['response']['code'] == 200 ) {
            return json_decode( wp_remote_retrieve_body( $request ) );
        }

        return [];
    }

    public function get_jobs() {
        $request = wp_remote_get( $this->api_url . '/jobs?limit=500&state=published',  );

        $body = wp_remote_retrieve_body( $request );

        return json_decode( $body );
    }
}
