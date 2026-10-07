<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Partner_Invoice extends WC_Email {

    public function __construct() {
        $this->id          = 'partner_invoice';
        $this->title       = 'Agent Generated Invoice';
        $this->description = 'Invoice sent by agent to partner.';
        $this->heading    = 'Invoice from Agent';
        $this->subject    = 'Your Requested Invoice';
        $this->template_html = 'emails/custom-partner-invoice.php';

        add_action( 'send_partner_invoice', array( $this, 'trigger' ), 10, 3 );

        parent::__construct();
    }

    public function trigger( $partner_id, $agent_id, $product_ids ) {
        $this->recipient = get_userdata( $partner_id )->user_email;
        $this->agent     = get_userdata( $agent_id );
        $this->products  = $product_ids;

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }

        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content_html()
        );
    }
}
