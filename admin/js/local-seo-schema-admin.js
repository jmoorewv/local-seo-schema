jQuery(document).ready(function ($) {

    var $locationsContainer = $('#local-seo-locations-container');
    var $addNewLocationButton = $('#add-new-location');

    // Function to generate a unique ID for new locations.
    function generateUniqueId() {
        return 'loc_' + Date.now() + Math.floor(Math.random() * 1000);
    }

    // Add New Location button click handler.
    $addNewLocationButton.on('click', function () {
        var newLocationId = generateUniqueId();
        var template = wp.template('new-location-form'); // Get the Underscore.js template.
        var newLocationHtml = template({ locationId: newLocationId }); // Render the template.

        $locationsContainer.append(newLocationHtml);

        // Make the new block collapsible.
        $locationsContainer.find('.local-seo-location-block[data-location-id="' + newLocationId + '"]').each(function () {
            initCollapsibleBlock($(this));
            // Ensure food-related fields visibility is correctly set for new blocks.
            updateFoodFieldsVisibility($(this).find('.local-seo-business-type')); // Changed function call
        });
    });

    // Remove Location button click handler (delegated).
    $locationsContainer.on('click', '.remove-location', function () {
        if (confirm('Are you sure you want to remove this location? This action cannot be undone.')) {
            $(this).closest('.local-seo-location-block').remove();
        }
    });

    // Toggle collapsible block functionality.
    function initCollapsibleBlock($block) {
        $block.find('.postbox-header').on('click', function (e) {
            // If the clicked element or its closest parent is the 'remove-location' button,
            // prevent the toggle action and let the remove button's own handler fire.
            if ($(e.target).is('.remove-location') || $(e.target).closest('.remove-location').length) {
                return;
            }
            // Otherwise, toggle the 'closed' class on the block.
            $block.toggleClass('closed');
        });
    }

    // Initialize collapsible functionality for existing blocks on page load.
    $('.local-seo-location-block').each(function () {
        initCollapsibleBlock($(this));
        // Ensure food-related fields visibility is correctly set on page load.
        updateFoodFieldsVisibility($(this).find('.local-seo-business-type')); // Changed function call
    });

    // Update business name in the header when the name field changes.
    $locationsContainer.on('keyup', '.local-seo-location-name', function () {
        var $block = $(this).closest('.local-seo-location-block');
        var newName = $(this).val();
        $block.find('.postbox-header h2').text(newName || lssAdmin.i18n.newLocation);
    });

    // Function to toggle visibility of food-related fields.
    function updateFoodFieldsVisibility($selectElement) { // Renamed function for clarity
        var $block = $selectElement.closest('.local-seo-location-block');
        var selectedType = $selectElement.val();
        // Corrected target class name to match PHP
        var $foodFields = $block.find('.local-seo-food-fields');

        // This list needs to match the PHP list of food-related types
        if (selectedType === 'Restaurant' || selectedType === 'FoodEstablishment' || selectedType === 'Bakery' || selectedType === 'BarOrPub' || selectedType === 'Brewery' || selectedType === 'CafeOrCoffeeShop' || selectedType === 'Distillery' || selectedType === 'FastFoodRestaurant' || selectedType === 'IceCreamShop' || selectedType === 'Winery') {
            $foodFields.removeClass('hidden');
        } else {
            $foodFields.addClass('hidden');
        }
    }

    // Listen for changes on the business type dropdown.
    $locationsContainer.on('change', '.local-seo-business-type', function () {
        updateFoodFieldsVisibility($(this)); // Changed function call
    });

});
