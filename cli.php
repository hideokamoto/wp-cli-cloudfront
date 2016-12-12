<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}
require 'aws.phar';
use Aws\CloudFront\CloudFrontClient;


/**
 * WP-CLI commands for the AWS CloudFront.
 *
 * @subpackage commands/community
 * @maintainer Hidetaka Okamoto
 */
class WP_CLI_CloudFront extends WP_CLI_Command {
    private $version = "v0.1.0";
    private $aws_sdk_version = "v3";
    private $client;

    /**
     * Create Client
     *
     **/
    private function _create_client( string $profile , string $access_key, string $secret_key ) {
        $this->client = new CloudFrontClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'profile' => $profile,
        ]);
    }

    /**
     * Set default Option params
     *
     *
     **/
    private function _set_option_params( $options ) {
        if ( ! isset( $options['profile'] ) ) {
            $options['profile'] = 'default';
        }
        if ( ! isset( $options['access_key'] ) ) {
            $options['access_key'] = false;
        }
        if ( ! isset( $options['secret_key'] ) ) {
            $options['secret_key'] = false;
        }
        return $options;
    }

    /**
     * Create CloudFront Distribution
     *
     * @when before_wp_load
     */
    function create( $args, $assoc_args ) {
        $options = $this->_set_option_params( $assoc_args );
        $this->_create_client( $options['profile'], $options['access_key'], $options['secret_key'] );
        $result = $this->client->listDistributions();
        var_dump($result);
        return;
        $domain = cli\confirm( 'Do you want to overwrite', false );
        echo $domain;
    }

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
