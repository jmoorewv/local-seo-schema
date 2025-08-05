<?php
/**
 * Schema generation for the Local SEO Schema Plugin.
 *
 * @package Local_SEO_Schema
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side of the site.
 */
class Local_SEO_Schema_Generator {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version           The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Add LocalBusiness schema to the site's head.
     *
     * This function retrieves all saved locations and generates a JSON-LD script
     * for each, embedding it into the HTML head.
     *
     * @since    1.0.0
     */
    public function add_local_business_schema() {
        $locations = get_option( 'local_seo_schema_locations', array() );

        if ( empty( $locations ) ) {
            return;
        }

        foreach ( $locations as $location_id => $location_data ) {
            // Basic validation for required fields before generating schema.
            if ( empty( $location_data['name'] ) || empty( $location_data['address_street'] ) ||
                 empty( $location_data['address_locality'] ) || empty( $location_data['address_region'] ) ||
                 empty( $location_data['address_postalcode'] ) || empty( $location_data['address_country'] ) ) {
                continue; // Skip this location if essential data is missing.
            }

            $schema = array(
                '@context' => 'https://schema.org',
                '@type'    => ! empty( $location_data['type'] ) ? $location_data['type'] : 'LocalBusiness',
                'name'     => $location_data['name'],
                'address'  => array(
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $location_data['address_street'],
                    'addressLocality' => $location_data['address_locality'],
                    'addressRegion'   => $location_data['address_region'],
                    'postalCode'      => $location_data['address_postalcode'],
                    'addressCountry'  => $location_data['address_country'],
                ),
            );

            // Optional fields.
            if ( ! empty( $location_data['telephone'] ) ) {
                $schema['telephone'] = $location_data['telephone'];
            }
            if ( ! empty( $location_data['url'] ) ) {
                $schema['url'] = $location_data['url'];
            } else {
                // Fallback to site URL if no specific URL is provided for the location.
                $schema['url'] = home_url();
            }
            if ( ! empty( $location_data['image'] ) ) {
                $schema['image'] = $location_data['image'];
            }
            if ( ! empty( $location_data['price_range'] ) ) {
                $schema['priceRange'] = $location_data['price_range'];
            }

            // Geo coordinates.
            if ( ! empty( $location_data['latitude'] ) && ! empty( $location_data['longitude'] ) ) {
                $schema['geo'] = array(
                    '@type'    => 'GeoCoordinates',
                    'latitude'  => (float) $location_data['latitude'],
                    'longitude' => (float) $location_data['longitude'],
                );
            }

            // Opening hours.
            if ( ! empty( $location_data['opening_hours'] ) && is_array( $location_data['opening_hours'] ) ) {
                $all_opening_specs = array();

                // First, generate individual specs for each day and each time range.
                foreach ( $location_data['opening_hours'] as $day_abbr => $hours_string ) {
                    $hours_string = trim( $hours_string );
                    $schema_day_name = $this->get_day_of_week_schema_name( $day_abbr );

                    if ( ! empty( $hours_string ) ) {
                        if ( strtolower( $hours_string ) === 'closed' ) {
                            $all_opening_specs[] = array(
                                'dayOfWeek' => 'https://schema.org/' . $schema_day_name,
                                'opens'     => '00:00',
                                'closes'    => '00:00',
                            );
                        } else {
                            // Handle multiple time ranges for a single day.
                            $time_ranges = explode( ',', $hours_string );
                            foreach ( $time_ranges as $range ) {
                                $range = trim( $range );
                                if ( preg_match( '/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $range, $matches ) ) {
                                    $all_opening_specs[] = array(
                                        'dayOfWeek' => 'https://schema.org/' . $schema_day_name,
                                        'opens'     => $matches[1],
                                        'closes'    => $matches[2],
                                    );
                                }
                            }
                        }
                    }
                }

                // Now, group the individual specs by their unique opens/closes times.
                $grouped_specs = array();
                foreach ( $all_opening_specs as $spec ) {
                    $key = $spec['opens'] . '-' . $spec['closes'];
                    if ( ! isset( $grouped_specs[ $key ] ) ) {
                        $grouped_specs[ $key ] = array(
                            '@type' => 'OpeningHoursSpecification',
                            'dayOfWeek' => array(),
                            'opens' => $spec['opens'],
                            'closes' => $spec['closes'],
                        );
                    }
                    // Add the dayOfWeek URI to the array for this group.
                    if ( ! in_array( $spec['dayOfWeek'], $grouped_specs[ $key ]['dayOfWeek'] ) ) {
                        $grouped_specs[ $key ]['dayOfWeek'][] = $spec['dayOfWeek'];
                    }
                }

                // Convert grouped specs back to a simple array for the schema.
                $schema['openingHoursSpecification'] = array_values( $grouped_specs );
            }

            // Area served.
            if ( ! empty( $location_data['area_served'] ) && is_array( $location_data['area_served'] ) ) {
                $area_served_array = array();
                foreach ( $location_data['area_served'] as $area ) {
                    if ( ! empty( $area ) ) {
                        $area_served_array[] = array(
                            '@type' => 'Place',
                            'name'  => $area,
                        );
                    }
                }
                if ( ! empty( $area_served_array ) ) {
                    $schema['areaServed'] = $area_served_array;
                }
            }

            // Map URL.
            if ( ! empty( $location_data['has_map'] ) ) {
                $schema['hasMap'] = $location_data['has_map'];
            }

            // Food-related business types and their specific fields.
            $food_business_types = array( 'Restaurant', 'FoodEstablishment', 'Bakery', 'BarOrPub', 'Brewery', 'CafeOrCoffeeShop', 'Distillery', 'FastFoodRestaurant', 'IceCreamShop', 'Winery' );
            if ( in_array( $location_data['type'], $food_business_types ) ) {
                if ( ! empty( $location_data['serves_cuisine'] ) && is_array( $location_data['serves_cuisine'] ) ) {
                    $serves_cuisine_array = array_filter( $location_data['serves_cuisine'] ); // Remove empty entries.
                    if ( ! empty( $serves_cuisine_array ) ) {
                        $schema['servesCuisine'] = $serves_cuisine_array;
                    }
                }
                if ( ! empty( $location_data['accepts_reservations'] ) ) {
                    $schema['acceptsReservations'] = ( 'True' === $location_data['accepts_reservations'] ) ? 'True' : 'False';
                }
                // NEW: Menu URL
                if ( ! empty( $location_data['menu_url'] ) ) {
                    $schema['menu'] = $location_data['menu_url'];
                }
            }

            // Output the JSON-LD script.
            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
        }
    }

    /**
     * Converts day abbreviation to Schema.org day of week name.
     *
     * @since 1.0.0
     * @param string $day_abbr Two-letter day abbreviation (e.g., Mo, Tu).
     * @return string Schema.org day of week name (e.g., Monday, Tuesday).
     */
    private function get_day_of_week_schema_name( $day_abbr ) {
        switch ( $day_abbr ) {
            case 'Mo': return 'Monday';
            case 'Tu': return 'Tuesday';
            case 'We': return 'Wednesday';
            case 'Th': return 'Thursday';
            case 'Fr': return 'Friday';
            case 'Sa': return 'Saturday';
            case 'Su': return 'Sunday';
            default: return '';
        }
    }
}
