<?php
/**
 * Admin settings page for the Local SEO Schema Plugin.
 *
 * @package Local_SEO_Schema
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area.
 */
class Local_SEO_Schema_Admin {

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
     * Add the plugin's admin menu page.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_options_page(
            __( 'Local SEO Schema Settings', 'local-seo-schema' ), // Page title
            __( 'Local SEO Schema', 'local-seo-schema' ),          // Menu title
            'manage_options',                                      // Capability required to access
            $this->plugin_name,                                    // Menu slug
            array( $this, 'display_plugin_admin_page' )            // Callback function to display the page
        );
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register a setting for storing all locations.
        register_setting(
            'local_seo_schema_options_group',             // Option group name
            'local_seo_schema_locations',                 // Option name
            array( $this, 'sanitize_locations_data' )     // Sanitize callback
        );

        // Add a settings section.
        add_settings_section(
            'local_seo_schema_main_section',                       // ID
            __( 'Manage Business Locations', 'local-seo-schema' ), // Title
            array( $this, 'print_section_info' ),                  // Callback to print description
            $this->plugin_name                                     // Page slug
        );

        // No individual fields needed, as we'll manage locations via a custom UI.
    }

    /**
     * Print the section text for the settings page.
     *
     * @since    1.0.0
     */
    public function print_section_info() {
        echo '<p>' . esc_html__( 'Add and manage your business locations here. Each location will generate its own LocalBusiness schema.', 'local-seo-schema' ) . '</p>';
    }

    /**
     * Sanitize the locations data before saving.
     *
     * @since    1.0.0
     * @param    array    $input    The input data from the form.
     * @return   array    The sanitized data.
     */
    public function sanitize_locations_data( $input ) {
        $new_input = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $location_id => $location ) {
                // Ensure location_id is valid.
                $location_id = sanitize_key( $location_id );

                $new_input[ $location_id ] = array(
                    'name'              => sanitize_text_field( $location['name'] ),
                    'type'              => sanitize_text_field( $location['type'] ),
                    'address_street'    => sanitize_text_field( $location['address_street'] ),
                    'address_locality'  => sanitize_text_field( $location['address_locality'] ),
                    'address_region'    => sanitize_text_field( $location['address_region'] ),
                    'address_postalcode'=> sanitize_text_field( $location['address_postalcode'] ),
                    'address_country'   => sanitize_text_field( $location['address_country'] ),
                    'telephone'         => sanitize_text_field( $location['telephone'] ),
                    'url'               => esc_url_raw( $location['url'] ),
                    'image'             => esc_url_raw( $location['image'] ),
                    'price_range'       => sanitize_text_field( $location['price_range'] ),
                    'latitude'          => (float) $location['latitude'],
                    'longitude'         => (float) $location['longitude'],
                    'opening_hours'     => array_map( 'sanitize_text_field', (array) $location['opening_hours'] ),
                    'area_served'       => array_map( 'sanitize_text_field', (array) $location['area_served'] ),
                    'has_map'           => esc_url_raw( $location['has_map'] ),
                    'serves_cuisine'    => array_map( 'sanitize_text_field', (array) $location['serves_cuisine'] ),
                    'accepts_reservations' => sanitize_text_field( $location['accepts_reservations'] ),
                    'menu_url'          => esc_url_raw( $location['menu_url'] ),
                );
            }
        }
        return $new_input;
    }

    /**
     * Display the plugin's admin settings page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get existing locations.
        $locations = get_option( 'local_seo_schema_locations', array() );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'local_seo_schema_options_group' );
                do_settings_sections( $this->plugin_name );
                ?>

                <div id="local-seo-locations-container">
                    <?php
                    if ( ! empty( $locations ) ) {
                        foreach ( $locations as $id => $location ) {
                            $this->render_location_form( $id, $location );
                        }
                    }
                    ?>
                </div>

                <button type="button" id="add-new-location" class="button button-secondary">
                    <?php esc_html_e( 'Add New Location', 'local-seo-schema' ); ?>
                </button>

                <?php submit_button( __( 'Save Changes', 'local-seo-schema' ) ); ?>
            </form>
        </div>

        <script type="text/html" id="tmpl-new-location-form">
            <?php $this->render_location_form( '{{data.locationId}}', array() ); ?>
        </script>

        <?php
    }

    /**
     * Renders the HTML form for a single location.
     *
     * @since 1.0.0
     * @param string $id       The unique ID for the location.
     * @param array  $location The location data.
     */
    private function render_location_form( $id, $location = array() ) {
        $name               = isset( $location['name'] ) ? $location['name'] : '';
        $type               = isset( $location['type'] ) ? $location['type'] : 'LocalBusiness';
        $address_street     = isset( $location['address_street'] ) ? $location['address_street'] : '';
        $address_locality   = isset( $location['address_locality'] ) ? $location['address_locality'] : '';
        $address_region     = isset( $location['address_region'] ) ? $location['address_region'] : '';
        $address_postalcode = isset( $location['address_postalcode'] ) ? $location['address_postalcode'] : '';
        $address_country    = isset( $location['address_country'] ) ? $location['address_country'] : '';
        $telephone          = isset( $location['telephone'] ) ? $location['telephone'] : '';
        $url                = isset( $location['url'] ) ? $location['url'] : '';
        $image              = isset( $location['image'] ) ? $location['image'] : '';
        $price_range        = isset( $location['price_range'] ) ? $location['price_range'] : '';
        $latitude           = isset( $location['latitude'] ) ? $location['latitude'] : '';
        $longitude          = isset( $location['longitude'] ) ? $location['longitude'] : '';
        $opening_hours      = isset( $location['opening_hours'] ) ? (array) $location['opening_hours'] : array();
        $area_served        = isset( $location['area_served'] ) ? (array) $location['area_served'] : array();
        $has_map            = isset( $location['has_map'] ) ? $location['has_map'] : '';
        $serves_cuisine     = isset( $location['serves_cuisine'] ) ? (array) $location['serves_cuisine'] : array();
        $accepts_reservations = isset( $location['accepts_reservations'] ) ? $location['accepts_reservations'] : '';
        $menu_url           = isset( $location['menu_url'] ) ? $location['menu_url'] : '';

        // Predefined list of common LocalBusiness types for a dropdown.
        // Organized into optgroups for better user experience.
        $business_types = array(
            'General' => array(
                'LocalBusiness' => 'Local Business (General)',
            ),
            'Automotive' => array(
                'AutomotiveBusiness' => 'Automotive Business (General)',
                'AutoBodyShop'       => 'Auto Body Shop',
                'AutoDealer'         => 'Auto Dealer',
                'AutoPartsStore'     => 'Auto Parts Store',
                'AutoRental'         => 'Auto Rental',
                'AutoRepair'         => 'Auto Repair',
                'AutoWash'           => 'Auto Wash',
                'GasStation'         => 'Gas Station',
                'MotorcycleDealer'   => 'Motorcycle Dealer',
                'MotorcycleRepair'   => 'Motorcycle Repair',
            ),
            'Education & Childcare' => array(
                'ChildCare'          => 'Child Care',
            ),
            'Emergency Services' => array(
                'EmergencyService'   => 'Emergency Service (General)',
                'FireStation'        => 'Fire Station',
                'Hospital'           => 'Hospital',
                'PoliceStation'      => 'Police Station',
            ),
            'Entertainment & Arts' => array(
                'EntertainmentBusiness' => 'Entertainment Business (General)',
                'AdultEntertainment' => 'Adult Entertainment',
                'AmusementPark'      => 'Amusement Park',
                'ArtGallery'         => 'Art Gallery',
                'Casino'             => 'Casino',
                'ComedyClub'         => 'Comedy Club',
                'MovieTheater'       => 'Movie Theater',
                'NightClub'          => 'Night Club',
                'PerformingArtsTheater' => 'Performing Arts Theater',
            ),
            'Financial Services' => array(
                'FinancialService'   => 'Financial Service (General)',
                'AccountingService'  => 'Accounting Service',
                'AutomatedTeller'    => 'Automated Teller (ATM)',
                'BankOrCreditUnion'  => 'Bank or Credit Union',
                'InsuranceAgency'    => 'Insurance Agency',
            ),
            'Food & Drink' => array(
                'FoodEstablishment'  => 'Food Establishment (General)',
                'Bakery'             => 'Bakery',
                'BarOrPub'           => 'Bar or Pub',
                'Brewery'            => 'Brewery',
                'CafeOrCoffeeShop'   => 'Cafe or Coffee Shop',
                'Distillery'         => 'Distillery',
                'FastFoodRestaurant' => 'Fast Food Restaurant',
                'IceCreamShop'       => 'Ice Cream Shop',
                'Restaurant'         => 'Restaurant',
                'Winery'             => 'Winery',
            ),
            'Government & Public Service' => array(
                'GovernmentOffice'   => 'Government Office (General)',
                'PostOffice'         => 'Post Office',
                'Library'            => 'Library',
                'RecyclingCenter'    => 'Recycling Center',
                'TouristInformationCenter' => 'Tourist Information Center',
            ),
            'Health & Beauty' => array(
                'HealthAndBeautyBusiness' => 'Health & Beauty Business (General)',
                'BeautySalon'        => 'Beauty Salon',
                'DaySpa'             => 'Day Spa',
                'HairSalon'          => 'Hair Salon',
                'HealthClub'         => 'Health Club',
                'NailSalon'          => 'Nail Salon',
                'TattooParlor'       => 'Tattoo Parlor',
            ),
            'Home & Construction' => array(
                'HomeAndConstructionBusiness' => 'Home & Construction Business (General)',
                'Electrician'        => 'Electrician',
                'GeneralContractor'  => 'General Contractor',
                'HVACBusiness'       => 'HVAC Business',
                'HousePainter'       => 'House Painter',
                'Locksmith'          => 'Locksmith',
                'MovingCompany'      => 'Moving Company',
                'Plumber'            => 'Plumber',
                'RoofingContractor'  => 'Roofing Contractor',
            ),
            'Legal Services' => array(
                'LegalService'       => 'Legal Service (General)',
                'Attorney'           => 'Attorney',
                'Notary'             => 'Notary',
            ),
            'Lodging' => array(
                'LodgingBusiness'    => 'Lodging Business (General)',
                'BedAndBreakfast'    => 'Bed And Breakfast',
                'Campground'         => 'Campground',
                'Hostel'             => 'Hostel',
                'Hotel'              => 'Hotel',
                'Motel'              => 'Motel',
                'Resort'             => 'Resort',
            ),
            'Medical & Healthcare' => array(
                'MedicalBusiness'    => 'Medical Business (General - use if more specific not found)',
                'MedicalOrganization' => 'Medical Organization (General)',
                'CommunityHealth'    => 'Community Health Center',
                'Dentist'            => 'Dentist',
                'Dermatology'        => 'Dermatology Clinic',
                'DietNutrition'      => 'Diet & Nutrition Center',
                'Geriatric'          => 'Geriatric Clinic',
                'Gynecologic'        => 'Gynecologic Clinic',
                'MedicalClinic'      => 'Medical Clinic',
                'Midwifery'          => 'Midwifery Practice',
                'Nursing'            => 'Nursing Home',
                'Obstetric'          => 'Obstetric Clinic',
                'Oncologic'          => 'Oncologic Clinic',
                'Optician'           => 'Optician',
                'Optometric'         => 'Optometric Clinic',
                'Otolaryngologic'    => 'Otolaryngologic Clinic',
                'Pediatric'          => 'Pediatric Clinic',
                'Pharmacy'           => 'Pharmacy',
                'Physician'          => 'Physician',
                'Physiotherapy'      => 'Physiotherapy Clinic',
                'PlasticSurgery'     => 'Plastic Surgery Clinic',
            ),
            'Other Local Businesses' => array(
                'InternetCafe'       => 'Internet Cafe',
                'PawnShop'           => 'Pawn Shop',
                'ProfessionalService' => 'Professional Service (General)',
                'RadioStation'       => 'Radio Station',
                'SelfStorage'        => 'Self Storage',
                'ShoppingCenter'     => 'Shopping Center',
                'TelevisionStation'  => 'Television Station',
                'TravelAgency'       => 'Travel Agency',
                'DryCleaningOrLaundry' => 'Dry Cleaning or Laundry',
                'EmploymentAgency'   => 'Employment Agency',
            ),
            'Sports & Recreation' => array(
                'SportsActivityLocation' => 'Sports Activity Location (General)',
                'BowlingAlley'       => 'Bowling Alley',
                'ExerciseGym'        => 'Exercise Gym',
                'GolfCourse'         => 'Golf Course',
                'PublicSwimmingPool' => 'Public Swimming Pool',
                'SkiResort'          => 'Ski Resort',
                'SportsClub'         => 'Sports Club',
                'StadiumOrArena'     => 'Stadium or Arena',
                'TennisComplex'      => 'Tennis Complex',
            ),
            'Stores' => array(
                'Store'              => 'Store (General)',
                'BikeStore'          => 'Bike Store',
                'BookStore'          => 'Book Store',
                'ClothingStore'      => 'Clothing Store',
                'ComputerStore'      => 'Computer Store',
                'ConvenienceStore'   => 'Convenience Store',
                'DepartmentStore'    => 'Department Store',
                'ElectronicsStore'   => 'Electronics Store',
                'Florist'            => 'Florist',
                'FurnitureStore'     => 'Furniture Store',
                'GardenStore'        => 'Garden Store',
                'GroceryStore'       => 'Grocery Store',
                'HardwareStore'      => 'Hardware Store',
                'HobbyShop'          => 'Hobby Shop',
                'HomeGoodsStore'     => 'Home Goods Store',
                'JewelryStore'       => 'Jewelry Store',
                'LiquorStore'        => 'Liquor Store',
                'MensClothingStore'  => 'Men\'s Clothing Store',
                'MobilePhoneStore'   => 'Mobile Phone Store',
                'ShoeStore'          => 'Shoe Store',
                'SportingGoodsStore' => 'Sporting Goods Store',
                'ToyStore'           => 'Toy Store',
                'WholesaleStore'     => 'Wholesale Store',
            ),
        );

        // Days of the week for opening hours.
        $days_of_week = array(
            'Mo' => 'Monday',
            'Tu' => 'Tuesday',
            'We' => 'Wednesday',
            'Th' => 'Thursday',
            'Fr' => 'Friday',
            'Sa' => 'Saturday',
            'Su' => 'Sunday',
        );
        ?>
        <div class="local-seo-location-block postbox" data-location-id="<?php echo esc_attr( $id ); ?>">
            <div class="postbox-header">
                <h2 class="hndle ui-sortable-handle">
                    <?php
                    echo empty( $name ) ?
                        esc_html__( 'New Location', 'local-seo-schema' ) :
                        esc_html( $name );
                    ?>
                </h2>
                <div class="handle-actions">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php esc_html_e( 'Toggle panel', 'local-seo-schema' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="remove-location button button-link-delete">
                        <?php esc_html_e( 'Remove', 'local-seo-schema' ); ?>
                    </button>
                </div>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_name' ); ?>"><?php esc_html_e( 'Business Name', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_name' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][name]"
                                       value="<?php echo esc_attr( $name ); ?>"
                                       class="regular-text local-seo-location-name"
                                       required />
                                <p class="description"><?php esc_html_e( 'The official name of your business location.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_type' ); ?>"><?php esc_html_e( 'Business Type', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <select id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_type' ); ?>"
                                        name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][type]"
                                        class="regular-text local-seo-business-type">
                                    <?php foreach ( $business_types as $optgroup_label => $types_in_group ) : ?>
                                        <optgroup label="<?php echo esc_attr( $optgroup_label ); ?>">
                                            <?php foreach ( $types_in_group as $value => $label ) : ?>
                                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>>
                                                    <?php echo esc_html( $label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e( 'Choose the most specific type for your business from Schema.org.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_street' ); ?>"><?php esc_html_e( 'Street Address', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_street' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][address_street]"
                                       value="<?php echo esc_attr( $address_street ); ?>"
                                       class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_locality' ); ?>"><?php esc_html_e( 'Locality (City)', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_locality' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][address_locality]"
                                       value="<?php echo esc_attr( $address_locality ); ?>"
                                       class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_region' ); ?>"><?php esc_html_e( 'Region (State/Province)', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_region' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][address_region]"
                                       value="<?php echo esc_attr( $address_region ); ?>"
                                       class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_postalcode' ); ?>"><?php esc_html_e( 'Postal Code', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_postalcode' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][address_postalcode]"
                                       value="<?php echo esc_attr( $address_postalcode ); ?>"
                                       class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_country' ); ?>"><?php esc_html_e( 'Country', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_address_country' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][address_country]"
                                       value="<?php echo esc_attr( $address_country ); ?>"
                                       class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_telephone' ); ?>"><?php esc_html_e( 'Telephone', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="tel"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_telephone' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][telephone]"
                                       value="<?php echo esc_attr( $telephone ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'e.g., +1-555-123-4567', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_url' ); ?>"><?php esc_html_e( 'Website URL', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="url"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_url' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][url]"
                                       value="<?php echo esc_attr( $url ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'The official URL of this business location.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_image' ); ?>"><?php esc_html_e( 'Image URL', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="url"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_image' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][image]"
                                       value="<?php echo esc_attr( $image ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'A URL to a photo of the business (e.g., logo or storefront).', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_price_range' ); ?>"><?php esc_html_e( 'Price Range', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_price_range' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][price_range]"
                                       value="<?php echo esc_attr( $price_range ); ?>"
                                       class="small-text" />
                                <p class="description"><?php esc_html_e( 'e.g., $, $$, $$$, $$$$.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_latitude' ); ?>"><?php esc_html_e( 'Latitude', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_latitude' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][latitude]"
                                       value="<?php echo esc_attr( $latitude ); ?>"
                                       class="small-text" />
                                <p class="description"><?php esc_html_e( 'Geographical latitude of the business location.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_longitude' ); ?>"><?php esc_html_e( 'Longitude', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_longitude' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][longitude]"
                                       value="<?php echo esc_attr( $longitude ); ?>"
                                       class="small-text" />
                                <p class="description"><?php esc_html_e( 'Geographical longitude of the business location.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Opening Hours', 'local-seo-schema' ); ?></th>
                            <td>
                                <div class="local-seo-opening-hours-container">
                                    <?php foreach ( $days_of_week as $day_abbr => $day_name ) : ?>
                                        <div class="local-seo-opening-hour-row">
                                            <label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_opening_hours_' . $day_abbr ); ?>">
                                                <?php echo esc_html( $day_name ); ?>:
                                            </label>
                                            <input type="text"
                                                   id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_opening_hours_' . $day_abbr ); ?>"
                                                   name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][opening_hours][<?php echo esc_attr( $day_abbr ); ?>]"
                                                   value="<?php echo esc_attr( isset( $opening_hours[ $day_abbr ] ) ? $opening_hours[ $day_abbr ] : '' ); ?>"
                                                   placeholder="e.g., 09:00-17:00 or closed"
                                                   class="regular-text" />
                                        </div>
                                    <?php endforeach; ?>
                                    <p class="description"><?php esc_html_e( 'Enter opening hours in HH:MM-HH:MM format (e.g., 09:00-17:00). Use "closed" if applicable. For multiple ranges on a single day, separate with commas (e.g., 09:00-12:00, 13:00-17:00).', 'local-seo-schema' ); ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_area_served' ); ?>"><?php esc_html_e( 'Area Served', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_area_served' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][area_served][]"
                                       value="<?php echo esc_attr( implode( ', ', $area_served ) ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'Comma-separated list of areas served (e.g., "New York City", "Brooklyn").', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_has_map' ); ?>"><?php esc_html_e( 'Map URL', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="url"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_has_map' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][has_map]"
                                       value="<?php echo esc_attr( $has_map ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'A URL to a map of the business location (e.g., Google Maps link).', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <?php
                        // Define food-related business types for conditional field display.
                        $food_business_types = array( 'Restaurant', 'FoodEstablishment', 'Bakery', 'BarOrPub', 'Brewery', 'CafeOrCoffeeShop', 'Distillery', 'FastFoodRestaurant', 'IceCreamShop', 'Winery' );
                        $is_food_business = in_array( $type, $food_business_types );
                        ?>
                        <tr class="local-seo-food-fields <?php echo $is_food_business ? '' : 'hidden'; ?>">
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_serves_cuisine' ); ?>"><?php esc_html_e( 'Serves Cuisine', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="text"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_serves_cuisine' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][serves_cuisine][]"
                                       value="<?php echo esc_attr( implode( ', ', $serves_cuisine ) ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'Comma-separated list of cuisines served (e.g., "Italian", "Mexican"). Only for food-related business types.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr class="local-seo-food-fields <?php echo $is_food_business ? '' : 'hidden'; ?>">
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_accepts_reservations' ); ?>"><?php esc_html_e( 'Accepts Reservations', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <select id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_accepts_reservations' ); ?>"
                                        name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][accepts_reservations]"
                                        class="regular-text">
                                    <option value="" <?php selected( $accepts_reservations, '' ); ?>><?php esc_html_e( 'Select', 'local-seo-schema' ); ?></option>
                                    <option value="True" <?php selected( $accepts_reservations, 'True' ); ?>><?php esc_html_e( 'Yes', 'local-seo-schema' ); ?></option>
                                    <option value="False" <?php selected( $accepts_reservations, 'False' ); ?>><?php esc_html_e( 'No', 'local-seo-schema' ); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Does this business accept reservations? Only for food-related business types.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                        <tr class="local-seo-food-fields <?php echo $is_food_business ? '' : 'hidden'; ?>">
                            <th scope="row"><label for="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_menu_url' ); ?>"><?php esc_html_e( 'Menu URL', 'local-seo-schema' ); ?></label></th>
                            <td>
                                <input type="url"
                                       id="<?php echo esc_attr( $this->plugin_name . '_locations_' . $id . '_menu_url' ); ?>"
                                       name="local_seo_schema_locations[<?php echo esc_attr( $id ); ?>][menu_url]"
                                       value="<?php echo esc_attr( $menu_url ); ?>"
                                       class="regular-text" />
                                <p class="description"><?php esc_html_e( 'A URL to the business\'s online menu. Only for food-related business types.', 'local-seo-schema' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
