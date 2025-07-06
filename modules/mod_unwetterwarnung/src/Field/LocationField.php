<?php

/**
 * ___       __          __   __   __
 * |  |     |  |        |  | |  | |  |
 * |  |     |  |        |  | |  | |  |
 * |  |     |  |        |  | |  | |  |
 * |  |_____|  |________|  |_|  |_|  |
 * |_________|___________|____________|
 * 
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 * @copyright   (C) 2024 Whykiki. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @since       1.0.0
 */

namespace Whykiki\Module\Unwetterwarnung\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Location Field with autocomplete functionality
 * 
 * Provides a text input field with geocoding autocomplete suggestions
 * for location selection in the module configuration.
 *
 * @since 1.0.0
 */
class LocationField extends TextField
{
    /**
     * The form field type.
     *
     * @var string
     */
    protected $type = 'Location';

    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup.
     */
    protected function getInput(): string
    {
        // Get the field attributes
        $class = $this->class ? ' class="' . $this->class . ' location-field"' : ' class="location-field"';
        $disabled = $this->disabled ? ' disabled' : '';
        $readonly = $this->readonly ? ' readonly' : '';
        $required = $this->required ? ' required' : '';
        $autocomplete = $this->autocomplete ? ' autocomplete="' . $this->autocomplete . '"' : ' autocomplete="off"';
        
        // Build the input field
        $html = [];
        
        // Add the main input field
        $html[] = '<div class="location-field-container">';
        $html[] = sprintf(
            '<input type="text" name="%s" id="%s" value="%s"%s%s%s%s%s placeholder="%s" data-location-field="true" />',
            $this->name,
            $this->id,
            htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'),
            $class,
            $disabled,
            $readonly,
            $required,
            $autocomplete,
            Text::_('MOD_UNWETTERWARNUNG_FIELD_LOCATION_PLACEHOLDER')
        );
        
        // Add coordinates display
        $html[] = '<div class="location-coordinates" id="' . $this->id . '_coordinates" style="display: none;">';
        $html[] = '<small class="text-muted">';
        $html[] = '<span class="coordinates-label">' . Text::_('MOD_UNWETTERWARNUNG_FIELD_LOCATION_COORDINATES') . ':</span> ';
        $html[] = '<span class="coordinates-value"></span>';
        $html[] = '</small>';
        $html[] = '</div>';
        
        // Add autocomplete suggestions container
        $html[] = '<div class="location-suggestions" id="' . $this->id . '_suggestions"></div>';
        
        // Add hidden fields for coordinates
        $html[] = sprintf(
            '<input type="hidden" name="%s_lat" id="%s_lat" value="%s" />',
            $this->fieldname,
            $this->id,
            htmlspecialchars($this->getCoordinate('lat'), ENT_COMPAT, 'UTF-8')
        );
        
        $html[] = sprintf(
            '<input type="hidden" name="%s_lon" id="%s_lon" value="%s" />',
            $this->fieldname,
            $this->id,
            htmlspecialchars($this->getCoordinate('lon'), ENT_COMPAT, 'UTF-8')
        );
        
        $html[] = '</div>';
        
        // Add the JavaScript for autocomplete functionality
        $this->addLocationFieldScript();
        
        return implode('', $html);
    }
    
    /**
     * Method to get the field label markup.
     *
     * @return string The field label markup.
     */
    protected function getLabel(): string
    {
        $label = parent::getLabel();
        
        // Add help text
        $helpText = Text::_('MOD_UNWETTERWARNUNG_FIELD_LOCATION_HELP');
        if ($helpText) {
            $label .= '<div class="form-text text-muted small">' . $helpText . '</div>';
        }
        
        return $label;
    }
    
    /**
     * Get coordinate value from stored data
     * 
     * @param string $type Either 'lat' or 'lon'
     * 
     * @return string Coordinate value
     */
    private function getCoordinate(string $type): string
    {
        $form = $this->form;
        $fieldName = $this->fieldname . '_' . $type;
        
        if ($form && $form->getValue($fieldName)) {
            return $form->getValue($fieldName);
        }
        
        return '';
    }
    
    /**
     * Add JavaScript for location field functionality
     */
    private function addLocationFieldScript(): void
    {
        $doc = $this->getDocument();
        
        // Add CSS for location field
        $css = '
        .location-field-container {
            position: relative;
        }
        
        .location-field {
            width: 100%;
            padding-right: 40px;
        }
        
        .location-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .location-suggestion {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .location-suggestion:hover {
            background-color: #f8f9fa;
        }
        
        .location-suggestion:last-child {
            border-bottom: none;
        }
        
        .location-coordinates {
            margin-top: 5px;
        }
        
        .coordinates-label {
            font-weight: 500;
        }
        ';
        
        $doc->addStyleDeclaration($css);
        
        // Add JavaScript for autocomplete
        $js = '
        document.addEventListener("DOMContentLoaded", function() {
            const locationFields = document.querySelectorAll("[data-location-field]");
            
            locationFields.forEach(function(field) {
                const fieldId = field.id;
                const suggestionsContainer = document.getElementById(fieldId + "_suggestions");
                const coordinatesContainer = document.getElementById(fieldId + "_coordinates");
                const latField = document.getElementById(fieldId + "_lat");
                const lonField = document.getElementById(fieldId + "_lon");
                
                let searchTimeout;
                
                // Handle input changes
                field.addEventListener("input", function() {
                    const query = this.value.trim();
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length < 3) {
                        hideSuggestions();
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        searchLocations(query);
                    }, 300);
                });
                
                // Handle focus out
                field.addEventListener("blur", function() {
                    setTimeout(function() {
                        hideSuggestions();
                    }, 200);
                });
                
                // Search locations using OpenWeatherMap Geocoding API
                function searchLocations(query) {
                    // For now, we use a simple mock - in real implementation,
                    // this would make an API call to OpenWeatherMap Geocoding API
                    const mockResults = [
                        { name: "Berlin, DE", lat: 52.5200, lon: 13.4050 },
                        { name: "Munich, DE", lat: 48.1351, lon: 11.5820 },
                        { name: "Hamburg, DE", lat: 53.5511, lon: 9.9937 }
                    ].filter(item => item.name.toLowerCase().includes(query.toLowerCase()));
                    
                    showSuggestions(mockResults);
                }
                
                // Show suggestions
                function showSuggestions(results) {
                    suggestionsContainer.innerHTML = "";
                    
                    if (results.length === 0) {
                        hideSuggestions();
                        return;
                    }
                    
                    results.forEach(function(result) {
                        const suggestion = document.createElement("div");
                        suggestion.className = "location-suggestion";
                        suggestion.textContent = result.name;
                        suggestion.addEventListener("click", function() {
                            selectLocation(result);
                        });
                        suggestionsContainer.appendChild(suggestion);
                    });
                    
                    suggestionsContainer.style.display = "block";
                }
                
                // Hide suggestions
                function hideSuggestions() {
                    suggestionsContainer.style.display = "none";
                }
                
                // Select location
                function selectLocation(location) {
                    field.value = location.name;
                    latField.value = location.lat;
                    lonField.value = location.lon;
                    
                    // Show coordinates
                    coordinatesContainer.querySelector(".coordinates-value").textContent = 
                        location.lat.toFixed(4) + ", " + location.lon.toFixed(4);
                    coordinatesContainer.style.display = "block";
                    
                    hideSuggestions();
                }
                
                // Initialize coordinates display if values exist
                if (latField.value && lonField.value) {
                    coordinatesContainer.querySelector(".coordinates-value").textContent = 
                        parseFloat(latField.value).toFixed(4) + ", " + parseFloat(lonField.value).toFixed(4);
                    coordinatesContainer.style.display = "block";
                }
            });
        });
        ';
        
        $doc->addScriptDeclaration($js);
    }
    
    /**
     * Get the document object
     * 
     * @return \Joomla\CMS\Document\Document
     */
    private function getDocument()
    {
        return \Joomla\CMS\Factory::getDocument();
    }
} 
