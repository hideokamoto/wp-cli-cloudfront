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
     **/
    private function _set_option_params( array $options ) {
        if ( ! isset( $options['profile'] ) ) {
            $options['profile'] = 'default';
        }
        if ( ! isset( $options['access_key'] ) ) {
            $options['access_key'] = false;
        }
        if ( ! isset( $options['secret_key'] ) ) {
            $options['secret_key'] = false;
        }
        if ( ! isset( $options['format'] ) ) {
            $options['format'] = 'json';
        }
        return $options;
    }

    /**
     * List CloudFront Distributions
     *
     * ##OPTIONS
     * [--profile=<profile>]
     * : AWS-CLI's profile name
     *
     * [--acccess_key=<access_key>]
     * : IAM Access Key
     *
     * [--secret_key=<secret_key>]
     * : IAM Secret Key
     *
     * [--format=<format>]
     * : Render results in a specific format (Now only YAML support)
     * @when before_wp_load
     */
    function list( $args, $assoc_args ) {
        $options = $this->_set_option_params( $assoc_args );
        $this->_create_client( $options['profile'], $options['access_key'], $options['secret_key'] );
        $result = $this->client->listDistributions();
        WP_CLI\Utils\format_items( 'yaml', $result->get('DistributionList'), array() );
    }

    /**
     * Generate CloudFront Distribution Config
     *
     * ##OPTIONS
	 * [--domain=<domain>]
	 * : Your website domain
	 *
	 * [--origin=<origin>]
	 * : Your origin webserver address
	 *
     * [--profile=<profile>]
     * : AWS-CLI's profile name
     *
     * [--acccess_key=<access_key>]
     * : IAM Access Key
     *
     * [--secret_key=<secret_key>]
     * : IAM Secret Key
     * @when after_wp_load
     */
    function generate_config( $args, $assoc_args ) {
		if ( isset( $assoc_args['domain'] ) ) {
			$domain = $assoc_args['domain'];
		} else {
			$domain = trim( cli\prompt(
				'Your website Domain',
				$default = false,
				$marker = ': ',
				$hide = false
			) );
		}
		$domain = esc_attr( $domain );

		if ( isset( $assoc_args['origin'] ) ) {
			$origin = $assoc_args['origin'];
		} else {
			$origin = trim( cli\prompt(
				'Your origin webserver address',
				$default = false,
				$marker = ': ',
				$hide = false
			) );
		}
		$origin = esc_attr( $origin );
		$result = wp_remote_get('https://raw.githubusercontent.com/amimoto-ami/create-cf-dist-settings/master/cloudfront-input.json');
		if ( is_wp_error( $result ) ) {
			return;
		}
		$config = $result['body'];
		$config = preg_replace( '/example.com/', $domain, $config );
		$config = preg_replace( '/%origin-id%/', $domain, $config );
		$config = preg_replace( '/origin.example.com/', $origin, $config );
		$config = preg_replace( '/%caller-reference%/', time(), $config );
		echo $config;
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
