<?php if( ! class_exists( 'WP_Query_Geo' ) ) {
    class WP_Query_Geo extends WP_Query 
    {
        private $lat = NULL;
        private $lng = NULL;
        private $dist = NULL;

        function __construct($args = []) 
        {
            if( !empty( $args['lat'] ) )
                $this->lat = $args['lat'];
          
            if( !empty( $args['lng'] ) )
                $this->lng = $args['lng'];

            if( !empty( $args['distance'] ) )
                $this->dist = $args['distance'];

            if( !empty( $args['lat'] ) ) {
                add_filter( 'posts_fields', [$this, 'posts_fields'], 10, 2 );
                add_filter( 'posts_join', [$this, 'posts_join'], 10, 2 );
                add_filter( 'posts_where', [$this, 'posts_where'], 10, 2 );
                add_filter( 'posts_orderby', [$this, 'posts_orderby'], 10, 2 );
            }

            unset( $args['lat'], $args['lng'], $args['distance'] );
            parent::query($args);

            # remove the filters again at the end (Resets for normal wp queries)
            $this->remove_filters();
        }

        /**
         * Selects the distance from a haversine formula
         */   
        public function posts_fields($fields)
        {
            global $wpdb;
        
            $fields .= sprintf(", ( 3959 * acos( 
                                cos( radians( %s ) ) * 
                                cos( radians( lat.meta_value ) ) * 
                                cos( radians( lng.meta_value ) - radians( %s ) ) + 
                                sin( radians( %s ) ) * 
                                sin( radians( lat.meta_value ) ) 
                                ) ) AS distance ", $this->lat, $this->lng, $this->lat); 
        
            $fields .= ", lat.meta_value AS latitude ";
            $fields .= ", lng.meta_value AS longitude ";
        
            return $fields;
        } // END public function posts_join($join, $query)

        /**
         * Makes joins as necessary in order to select lat/long metadata
         */   
        public function posts_join($join, $query)
        {
            global $wpdb;

            $join .= " INNER JOIN {$wpdb->postmeta} AS lat ON {$wpdb->posts}.ID = lat.post_id ";
            $join .= " INNER JOIN {$wpdb->postmeta} AS lng ON {$wpdb->posts}.ID = lng.post_id ";
        
            return $join;
        } // END public function posts_join($join, $query)

        /**
         * Adds where clauses to compliment joins
         */   
        public function posts_where($where)
        {
            $where .= ' AND lat.meta_key="address_lat" ';
            $where .= ' AND lng.meta_key="address_long" ';
            $where .= " HAVING distance < {$this->dist}";      

            return $where;
        } // END public function posts_where($where)
      
        /**
         * order posts by distance, then any other term
         * @param  string $orderby
         * @return string 
         */
        public function posts_orderby($orderby)
        {
            $orderby = " distance ASC, " . $orderby;  

            return $orderby;
        } // END public function posts_orderby($orderby)

        /**
         * remove the filters from the query (this ensures we can keep our other queries clean)
         * @return null
         */
        public function remove_filters()
        {
            remove_filter( 'posts_fields', [$this, 'posts_fields'], 10, 2 );
            remove_filter( 'posts_join', [$this, 'posts_join'], 10, 2 );
            remove_filter( 'posts_where', [$this, 'posts_where'], 10, 2 );
            remove_filter( 'posts_orderby', [$this, 'posts_orderby'], 10, 2 );
        }
    } 
}
