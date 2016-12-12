<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}
require 'aws.phar';

/**
 * WP-CLI commands for the AWS CloudFront.
 *
 * @subpackage commands/community
 * @maintainer Hidetaka Okamoto
 */
class WP_CLI_CloudFront extends WP_CLI_Command {
    private $version = "v0.1.0";
    private $aws_sdk_version = "v3";

    /**
     * Prints current version of the cli command.
     *
     * @when before_wp_load
     */
    public function version(){
        WP_CLI::line( $this->version );
    }

    /**
     * Prints current version of AWS SDK.
     *
     * @when before_wp_load
     */
    public function sdk_version(){
        WP_CLI::line( $this->aws_sdk_version );
    }
}
WP_CLI::add_command( 'cloudfront', 'WP_CLI_CloudFront' );
