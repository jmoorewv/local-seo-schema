# Local SEO Schema Plugin

The Local SEO Schema Plugin is a powerful WordPress plugin designed to help local businesses improve their search engine visibility by adding structured data (JSON-LD) to their website. This plugin allows you to define multiple business locations, each with its own comprehensive set of details, including name, address, contact information, opening hours, geographical coordinates, and more.

By embedding this schema directly into your website's source code, you provide search engines like Google with clear, machine-readable information about your business, making it easier for them to understand your offerings and display rich results in local search queries, Google Maps, and knowledge panels.

## Features

* **Multiple Location Support:** Define and manage an unlimited number of business locations.

* **Comprehensive Schema Properties:** Includes essential LocalBusiness schema properties:

    * Business Name

    * Specific Business Type (e.g., Restaurant, Dentist, HairSalon)

    * Full Postal Address (Street, Locality, Region, Postal Code, Country)

    * Telephone Number

    * Website URL

    * Image URL (e.g., logo, storefront photo)

    * Price Range

    * Geographical Coordinates (Latitude, Longitude)

    * Detailed Opening Hours (supports multiple ranges per day, closed days)

    * Area Served (for service-based businesses)

    * Map URL (e.g., Google Maps link)

    * Restaurant-specific properties (Serves Cuisine, Accepts Reservations, Menu Link)

* **JSON-LD Output:** Generates clean, valid JSON-LD schema, the preferred format for Google.

* **Admin Interface:** User-friendly settings page in the WordPress dashboard for easy management of locations.

* **Collapsible Location Blocks:** Organize your locations in the admin panel with collapsible sections.

* **Dynamic Field Visibility:** Restaurant-specific fields appear only when a relevant business type is selected.

* **WordPress Best Practices:** Developed following WordPress coding standards and security guidelines.

## Installation

1.  **Download the plugin:** (If this were a real plugin, you'd download a ZIP file here).

2.  **Upload via WordPress Dashboard:**

    * Go to `Plugins > Add New` in your WordPress admin.

    * Click the `Upload Plugin` button.

    * Choose the `local-seo-schema.zip` file you downloaded and click `Install Now`.

    * Once installed, click `Activate Plugin`.

3.  **Manual Installation (FTP):**

    * Unzip the `local-seo-schema.zip` file.

    * Upload the entire `local-seo-schema` folder to your `wp-content/plugins/` directory via FTP.

    * Go to `Plugins` in your WordPress admin and click `Activate` next to "Local SEO Schema Plugin".

## Usage

1.  **Access Settings:** After activation, navigate to `Settings > Local SEO Schema` in your WordPress admin dashboard.

2.  **Add New Location:** Click the "Add New Location" button to add a new business entry.

3.  **Fill in Details:**

    * **Business Name:** The official name of your business location.

    * **Business Type:** Select the most specific type that describes your business from the dropdown list (e.g., `Restaurant`, `Dentist`, `HairSalon`).

    * **Address Details:** Enter the complete street address, city, state/region, postal code, and country.

    * **Telephone:** Your primary contact number (e.g., `+1-555-123-4567`).

    * **Website URL:** The official URL for this specific business location.

    * **Image URL:** A direct URL to an image of your business (e.g., logo, storefront).

    * **Price Range:** Use symbols like `$`, `$$`, `$$$`, `$$$$` to indicate price range.

    * **Latitude & Longitude:** Geographical coordinates for precise location. You can find these using Google Maps.

    * **Opening Hours:** Enter daily opening hours in `HH:MM-HH:MM` format (e.g., `09:00-17:00`). Use `closed` for days the business is not open. For multiple ranges on a single day, separate with commas (e.g., `09:00-12:00, 13:00-17:00`).

    * **Area Served:** For service-based businesses, list comma-separated cities or regions your business serves from this location (e.g., `Oak Park, Glenview`).

    * **Map URL:** A direct link to your business's location on a map (e.g., Google Maps URL).

    * **Serves Cuisine (Restaurant):** (Appears for food-related types) Comma-separated list of cuisines served (e.g., `Italian, Mexican`).

    * **Accepts Reservations (Restaurant):** (Appears for food-related types) Indicate if the business accepts reservations.

4.  **Save Changes:** After adding or modifying locations, click the "Save Changes" button at the bottom of the page.

The plugin will automatically inject the generated JSON-LD schema into the `<head>` section of your website's pages. You can verify the schema using Google's [Rich Results Test](https://search.google.com/test/rich-results) or Schema.org's [Schema Markup Validator](https://validator.schema.org/).

## Changelog

**1.0.0 - 2025-08-05**

* Initial release.

* Core functionality for adding multiple LocalBusiness schema types.

* Support for all essential LocalBusiness properties.

* Admin settings page for managing locations.

* Dynamic grouping of opening hours.

* Expanded list of specific LocalBusiness subtypes with optgroups.

## Frequently Asked Questions

**What is JSON-LD schema and why do I need it?**

JSON-LD (JavaScript Object Notation for Linked Data) is a lightweight data format used to structure data on your website. For local businesses, it tells search engines specific details like your name, address, phone number, and opening hours in a way they can easily understand. This helps improve your visibility in local search results, Google Maps, and can lead to rich snippets.

**Can I add multiple locations?**

Yes, the plugin is designed to support an unlimited number of distinct physical business locations. Each location will generate its own LocalBusiness schema.

**How do I specify service areas if I don't have a physical location there?**

Use the "Area Served" field for your primary physical location. List the cities or regions where you provide services but do not have a separate physical storefront or office. Do not create a new location entry for service-only areas.

**How do I find my Latitude and Longitude?**

You can easily find these by searching for your business on Google Maps. Right-click on your business location on the map, and the coordinates will usually appear in the context menu or the URL.

**How do I delete a business location entry?**

To remove a location, simply click the **"Remove"** button located in the top-right corner of each individual location block on the plugin's settings page. A confirmation prompt will appear to ensure you want to delete the entry. After confirming, remember to click the **"Save Changes"** button at the bottom of the page to finalize the deletion.

**My schema isn't showing up or isn't valid. What should I do?**

1.  Ensure the plugin is activated.

2.  Verify that you have saved your location settings in the admin panel.

3.  Check your website's source code for the `<script type="application/ld+json">` tags in the `<head>` section.

4.  Use Google's [Rich Results Test](https://search.google.com/test/rich-results) or Schema.org's [Schema Markup Validator](https://validator.schema.org/) to test your page. These tools will highlight any errors or warnings.

5.  Ensure all required fields (Name, Address) are filled for each location.

## Support

If you encounter any issues or have questions, **please open a new issue on the plugin's GitHub repository**. This is the best way to get support, report bugs, or suggest new features.

## Contributing

Contributions are welcome! If you have suggestions or find bugs, please consider submitting a pull request or opening an issue on the plugin's GitHub repository.
