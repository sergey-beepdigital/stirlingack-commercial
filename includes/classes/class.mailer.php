<?php

/**
 * Class WP_Mailer
 * @author Stepnev Sergey
 * @description Allow to send emails
 */
class WP_Mailer {
    private $headers = array();
    private $data = array();
    private $type = '';
    private $subject = '';
    private $recipients = array();
    private $users = array();
    private $attachments = array();
    private $debug = false;

    function __construct() {
        $this->headers[] = 'Content-Type: text/html; charset=UTF-8';
    }

    /**
     * Set debug to get results in file
     */
    public function set_debug() {
        $this->debug = true;

        return $this;
    }

    /**
     * Set header line in array of headers
     * @param string $header_item
     * @return $this
     */
    public function set_header_line($header_item = '') {
        if(empty($header_item)) return $this;

        $this->headers[] = $header_item;

        return $this;
    }

    /** Set admin email
     * @return $this
     */
    public function set_admin_email() {
        $admin_email = get_option('admin_email');

        $this->add_recipient_email($admin_email);

        return $this;
    }

    /** Set user id
     * @param null $user_id
     * @return $this
     */
    public function set_user($user_id = null) {
        $this->users[] = $user_id;

        return $this;
    }

    /**
     * Set recipients which was assigned as IDs
     * @return $this
     */
    public function set_user_email() {
        $user_ids = $this->users;

        if(sizeof($user_ids) > 0) {
            foreach ($user_ids as $user_id) {
                $user = get_userdata($user_id);

                $this->recipients[] = $user->user_email;
            }
        }

        return $this;
    }

    /**
     * Add emails in recipients array
     * @param string $email
     * @return $this
     */
    public function add_recipient_email($email = '') {
        if(!empty($email)) {
            $this->recipients[] = $email;
        }

        return $this;
    }

    /** Set email data
     * @param array $data
     * @return $this
     */
    public function set_email_data($data = array()) {
        $this->data = $data;

        return $this;
    }

    /** Set Email Type
     * @param string $type
     * @return $this
     */
    public function set_type($type = '') {
        $this->type = $type;

        return $this;
    }

    public function set_subject($subject = '') {
        $this->subject = $subject;

        return $this;
    }

    /** Set Recipients as array
     * @param array $recipients
     * @return $this
     */
    public function set_recepients($recipients = array()) {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Add single attachment to the attachments array
     * @param string $file_path
     * @return $this
     */
    public function add_attachment($file_path = '') {
        if(!empty($file_path)) {
            $this->attachments[] = $file_path;
        }

        return $this;
    }

    /** Sent email
     * @return bool
     */
    public function send() {
        if(empty($this->type)) die('Mailer: Type for ' . $this->type . ' is not defined');;

        if(empty($this->subject)) die('Mailer: Subject for ' . $this->subject . ' is not defined');

        $context = Timber::get_context();

        $context['body'] = $this->data;

        $headers = apply_filters('mailer_mail_headers', $this->headers);
        $subject = apply_filters('mailer_mail_subject', $this->subject);
        $body = Timber::compile('templates/emails/' . str_replace(array('_',' '), '-', $this->type) . '.twig', $context);

        // Custom Style
        $body = $this->wrapp_message($body, $this->type);

        /*echo '<pre>';print_r($this->recipients);echo '</pre>';
        echo '<pre>';print_r($subject);echo '</pre>';
        echo '<pre>';print_r($body);echo '</pre>';die;*/

        //$this->send_notification();

        // TODO: Remove after tests
        //$this->recipients = array('infrastructure@thisiscrowd.com','sergey@thisiscrowd.com');

        if(!$this->debug) {
            $mail_sent = wp_mail($this->recipients, $subject, $body, $headers, $this->attachments);

            $this->clear_data();

            return $mail_sent;
        } else {
            write_log(array(
                'headers'     => $this->headers,
                'recipients'  => $this->recipients,
                'subject'     => $subject,
                'attachments' => $this->attachments,
                'body'        => $body,
            ));

            file_put_contents(TEMPLATEPATH . '/email_test.html',$body);

            $this->clear_data();

            return true;
        }
    }

    /**
     * Allow to add custom design for emails
     * @param string $message
     * @param string $type
     * @return string
     */
    public function wrapp_message($message = '', $type = '') {
        // Buffer.
        ob_start();

        do_action( 'mailer_header', $type );

        echo wpautop( wptexturize( $message ) ); // WPCS: XSS ok.

        do_action( 'mailer_footer', $type );

        // Get contents.
        $message = ob_get_clean();

        return $message;
    }

    /**
     * Clear email data for the next correct send
     */
    public function clear_data() {
        $this->type = '';
        $this->recipients = array();
        $this->data = array();
        $this->users = array();
    }
}
