import { nextSlide, swiperSab } from "./main";

/**
 * Form Validation Module
 * Handles all form validation logic with comprehensive error checking
 */
const formValidator = {
  /**
   * Validates all form fields
   * @returns {Object} Validation result with isValid flag and errors array
   */
  validate() {
    const errors = [];

    // Validate distance range
    const distanceRange = document.getElementById("sab-distance-range");
    if (!distanceRange || !distanceRange.value) {
      errors.push("El rango de distancia es requerido");
    }

    // Validate voltage selection
    const voltageSelected = document.querySelector(
      'input[name="voltage"]:checked'
    );
    if (!voltageSelected) {
      errors.push("La especificación de voltaje es requerida");
    }

    // Validate amperage selection
    const amperageSelected = document.querySelector(
      'input[name="amperage"]:checked'
    );
    if (!amperageSelected) {
      errors.push("La especificación de amperaje es requerida");
    }

    // Validate battery location
    const locationSelected = document.querySelector(
      'input[name="ubicacion-de-bateria"]:checked'
    );
    if (!locationSelected) {
      errors.push("La ubicación de la batería es requerida");
    }

    // Validate physical dimensions based on battery location
    const isExternalBattery = locationSelected && locationSelected.value === 'Externa';

    if (!isExternalBattery) {
      // Validate dimensions for internal battery only
      const height = document.getElementById("alto-bateria");
      const width = document.getElementById("ancho-bateria");
      const length = document.getElementById("largo-bateria");

      if (
        !height ||
        !height.value ||
        isNaN(height.value) ||
        parseFloat(height.value) <= 0
      ) {
        errors.push("Se requiere una altura válida (cm)");
      }

      if (
        !width ||
        !width.value ||
        isNaN(width.value) ||
        parseFloat(width.value) <= 0
      ) {
        errors.push("Se requiere un ancho válido (cm)");
      }

      if (
        !length ||
        !length.value ||
        isNaN(length.value) ||
        parseFloat(length.value) <= 0
      ) {
        errors.push("Se requiere una longitud válida (cm)");
      }
    }

    // Validate scooter model
    const scooterModel = document.getElementById("modelo-patinete");
    if (!scooterModel || !scooterModel.value.trim()) {
      errors.push("El modelo del patinete es requerido");
    }

    // Validate cantidad de motores
    const cantidadMotores = document.querySelector('input[name="cantidad-motores"]:checked');
    if (!cantidadMotores) {
      errors.push("La cantidad de motores es requerida");
    }

    // Validate connector type
    const connectorSelected = document.querySelector(
      'input[name="tipo-de-conector"]:checked'
    );
    if (!connectorSelected) {
      errors.push("El tipo de conector es requerido");
    }
    
    // If OTROS is selected, validate that custom connector text is provided
    if (connectorSelected && connectorSelected.value === 'OTROS') {
      const customConnectorInput = document.getElementById("text-input-conector");
      if (!customConnectorInput || !customConnectorInput.value.trim()) {
        errors.push("Por favor, especifica el nombre del conector personalizado");
      }
    }

    return {
      isValid: errors.length === 0,
      errors,
    };
  },
};

/**
 * Data Collection Module
 * Handles collecting form data with proper sanitization
 */
const dataCollector = {
  /**
   * Collects all form field values
   * @returns {Object} Form data object
   */
  collect() {
    const distanceRange = document.getElementById("sab-distance-range");
    const voltageSelected = document.querySelector(
      'input[name="voltage"]:checked'
    );
    const amperageSelected = document.querySelector(
      'input[name="amperage"]:checked'
    );
    const locationSelected = document.querySelector(
      'input[name="ubicacion-de-bateria"]:checked'
    );
    const isExternalBattery = locationSelected && locationSelected.value === 'Externa';
    
    const height = document.getElementById("alto-bateria");
    const width = document.getElementById("ancho-bateria");
    const length = document.getElementById("largo-bateria");
    const cantidadMotoresSelected = document.querySelector('input[name="cantidad-motores"]:checked');
    const scooterModel = document.getElementById("modelo-patinete");
    const connectorSelected = document.querySelector(
      'input[name="tipo-de-conector"]:checked'
    );
    
    // Get connector type value - if OTROS, use custom text input value
    let connectorType = connectorSelected ? connectorSelected.value : null;
    if (connectorType === 'OTROS') {
      const customConnectorInput = document.getElementById("text-input-conector");
      const customValue = customConnectorInput ? customConnectorInput.value.trim() : '';
      connectorType = customValue || 'OTROS';
    }

    // NEW: Get variation ID from hidden field
    const variationIdField = document.getElementById('variation-id');
    const variationId = variationIdField ? parseInt(variationIdField.value) : 0;

    return {
      variation_id: variationId, // NEW: Include variation ID
      electrical_specifications: {
        voltage: voltageSelected ? voltageSelected.value : null,
        amperage: amperageSelected ? amperageSelected.value : null,
        distance_range_km: distanceRange ? parseInt(distanceRange.value) : null,
      },
      physical_dimensions: {
        height_cm: height ? parseFloat(height.value) : null,
        width_cm: width ? parseFloat(width.value) : null,
        length_cm: length ? parseFloat(length.value) : null,
      },
      cantidad_motores: cantidadMotoresSelected ? parseInt(cantidadMotoresSelected.value) : null,
      scooter_model: scooterModel ? scooterModel.value.trim() : null,
      battery_location: locationSelected ? locationSelected.value : null,
      connector_type: connectorType,
    };
  },
};

/**
 * UI Management Module
 * Handles all UI interactions and feedback
 */
const uiManager = {
  /**
   * NEW: Display dynamic price based on voltage and amperage selections
   * @param {string} voltage - Selected voltage (e.g., "24V")
   * @param {string} amperage - Selected amperage (e.g., "4,8AH")
   */
  displayDynamicPrice(voltage, amperage) {
    const priceElement = document.getElementById('dynamic-price-display');
    if (!priceElement) return;
    
    // Get pricing matrix from window object (populated by AJAX)
    const pricingMatrix = window.batteryPricingMatrix || {};
    
    // Look up price directly from matrix: matrix[voltage][amperage]
    const finalPrice = pricingMatrix[voltage]?.[amperage];
    
    if (!finalPrice) {
      priceElement.textContent = 'Precio no disponible';
      priceElement.dataset.price = 0;
      return;
    }
    
    // Format price with currency (EUR for Spain)
    const formattedPrice = new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(finalPrice);
    
    priceElement.textContent = formattedPrice;
    priceElement.dataset.price = finalPrice;
  },

  /**
   * NEW: Update variation ID when voltage/amperage selections change
   * @param {string} voltage - Selected voltage
   * @param {string} amperage - Selected amperage
   */
  updateVariationId(voltage, amperage) {
    const variationField = document.getElementById('variation-id');
    if (!variationField) return;
    
    // Store voltage and amperage for server-side lookup
    variationField.dataset.voltage = voltage;
    variationField.dataset.amperage = amperage;
    
    // Optionally, you could make an AJAX call here to get the variation ID
    // For now, we'll let the server handle it during form submission
  },

  /**
   * Shows user feedback message
   * @param {string} message - Message to display
   * @param {string} type - Message type: 'success', 'error', 'info'
   * @param {HTMLElement} formElement - Form element to attach feedback to
   */
  showFeedback(message, type = "info", formElement) {
    // Remove existing feedback if present
    const existingFeedback = document.querySelector(".sabway-form-feedback");
    if (existingFeedback) {
      existingFeedback.remove();
    }

    const feedbackDiv = document.createElement("div");
    feedbackDiv.className = `sabway-form-feedback !p-4 !rounded-lg !mb-4 !text-white-eco ${
      type === "success"
        ? "!bg-green-eco !text-black-eco"
        : type === "error"
        ? "!bg-red-500"
        : "!bg-blue-eco"
    }`;
    feedbackDiv.textContent = message;

    formElement.appendChild(feedbackDiv);

    // Auto-remove success messages after 5 seconds
    if (type === "success") {
      setTimeout(() => {
        feedbackDiv.remove();
      }, 5000);
    }
  },

  /**
   * Sets loading state on submit button
   * @param {HTMLElement} submitButton - Submit button element
   * @param {boolean} isLoading - Loading state
   */
  setLoadingState(submitButton, isLoading) {
    if (isLoading) {
      submitButton.disabled = true;
      submitButton.classList.add("opacity-50", "cursor-not-allowed");
      submitButton.innerHTML =
        '<iconify-icon icon="eos-icons:loading" class="!align-middle !mr-2" width="16" height="16"></iconify-icon>Enviando...';
    } else {
      submitButton.disabled = false;
      submitButton.classList.remove("opacity-50", "cursor-not-allowed");
      submitButton.innerHTML = "Finalizar Pedido";
    }
  },
  updatesumary() {
    // Collect current form data
    const connectorRadio = document.querySelector('input[name="tipo-de-conector"]:checked');
    let connectorType = connectorRadio?.value || "No seleccionado";
    
    // If OTROS is selected, get the custom connector text value
    if (connectorType === 'OTROS') {
      const customConnectorInput = document.getElementById("text-input-conector");
      const customValue = customConnectorInput?.value.trim() || '';
      connectorType = customValue || 'OTROS';
    }
    
    const batteryLocation = document.querySelector('input[name="ubicacion-de-bateria"]:checked')?.value || "No seleccionado";
    const isExternalBattery = batteryLocation === 'Externa';
    
    const formData = {
      voltage:
        document.querySelector('input[name="voltage"]:checked')?.value ||
        "No seleccionado",
      amperage:
        document.querySelector('input[name="amperage"]:checked')?.value ||
        "No seleccionado",
      distanceRange:
        document.getElementById("sab-distance-range")?.value || "0",
      height: document.getElementById("alto-bateria")?.value || "0",
      width: document.getElementById("ancho-bateria")?.value || "0",
      length: document.getElementById("largo-bateria")?.value || "0",
      cantidadMotores: document.querySelector('input[name="cantidad-motores"]:checked')?.value || null,
      scooterModel:
        document.getElementById("modelo-patinete")?.value || "No especificado",
      batteryLocation: batteryLocation,
      connectorType: connectorType,
    };

    // Update confirmation fields with current values
    this.updateConfirmationField("voltios", formData.voltage);
    this.updateConfirmationField("amperios", formData.amperage);
    this.updateConfirmationField("autonomia", `${formData.distanceRange}km`);
    
    // Update dimension fields — only show rows for internal batteries
    const showDimensions = !isExternalBattery;
    this.updateConfirmationField("altocm", formData.height ? `${formData.height}cm` : "0cm");
    this.updateConfirmationField("anchocm", formData.width ? `${formData.width}cm` : "0cm");
    this.updateConfirmationField("largocm", formData.length ? `${formData.length}cm` : "0cm");
    this.toggleConfirmationRow("altocm", showDimensions);
    this.toggleConfirmationRow("anchocm", showDimensions);
    this.toggleConfirmationRow("largocm", showDimensions);

    // Update cantidad de motores
    this.updateConfirmationField("cantidad-motores", formData.cantidadMotores || "No seleccionado");

    this.updateConfirmationField(
      "modelo-de-patinete-elctrico",
      formData.scooterModel
    );
    this.updateConfirmationField(
      "ubicacin-de-bateria",
      formData.batteryLocation
    );
    this.updateConfirmationField("tipo-de-conector", formData.connectorType);
  },
  /**
   * Updates a specific confirmation field
   * @param {string} fieldName - The field name to update
   * @param {string} value - The value to display
   */
  updateConfirmationField(fieldName, value) {
    const confirmationElement = document.querySelectorAll(
      `.final-check-${fieldName}`
    );
    if (confirmationElement.length > 1) {
      confirmationElement.forEach((element) => {
        const valueElement = element.querySelector("p");
        if (valueElement) {
          valueElement.textContent = value;
        }
      });
    } else if (confirmationElement.length === 1) {
      const valueElement = confirmationElement.querySelector("p");
      if (valueElement) {
        valueElement.textContent = value;
      }
    }
  },
  
  /**
   * Toggles visibility of a confirmation row
   * @param {string} fieldName - The field name to toggle
   * @param {boolean} show - Whether to show or hide the row
   */
  toggleConfirmationRow(fieldName, show) {
    const confirmationElements = document.querySelectorAll(
      `.final-check-${fieldName}`
    );
    confirmationElements.forEach((element) => {
      if (show) {
        element.style.display = '';
      } else {
        element.style.display = 'none';
      }
    });
  },
};


/**
 * AJAX Submission Module
 * Handles adding products to cart via WordPress AJAX endpoint
 */
const ajaxSubmitter = {
  /**
   * Adds product to cart via AJAX
   * @param {Object} formData - Form data object
   * @returns {Promise} API response
   */
  async addToCart(formData) {
    try {
      // Get AJAX configuration
      const ajaxConfig =
        window.taller_sabway_ajax || window.ecolitio_ajax || {};
      const ajaxUrl =
        ajaxConfig.ajax_url ||
        `${window.location.origin}/wp-admin/admin-ajax.php`;
      
      // Try to get nonce from multiple sources
      let nonce = ajaxConfig.sabway_form_nonce || ajaxConfig.nonce || "";
      
      // Fallback: Try to get nonce from DOM input if not in global object
      if (!nonce) {
        const nonceInput = document.querySelector('input[name="ecolitio_sabway_nonce"]');
        if (nonceInput) {
          nonce = nonceInput.value;
          console.log("Nonce retrieved from DOM input");
        }
      }

      console.log("Adding to cart via AJAX");
      console.log("DEBUG nonce:", nonce ? `present (${nonce.substring(0,6)}...)` : "MISSING");

      // Get product ID from hidden input field (for shortcode compatibility)
      let productId = null;
      const productIdInput = document.querySelector('input[name="sabway_product_id"]');
      if (productIdInput) {
        productId = parseInt(productIdInput.value);
      }
      
      // Fallback: Try to get product ID from the page DOM element (for product pages)
      if (!productId) {
        const productElement = document.querySelector('[id^="product-"]');
        productId = productElement
          ? parseInt(productElement.id.replace("product-", ""))
          : null;
      }

      // Get battery type from form data attribute
      let batteryType = 'sabway'; // default
      const batteryTypeInput = document.querySelector('input[name="battery_type"]');
      if (batteryTypeInput) {
        batteryType = batteryTypeInput.value;
      }

      // Prepare FormData
      const formDataSubmit = new FormData();
      formDataSubmit.append("action", "custom_batery_add_to_cart");
      formDataSubmit.append("nonce", nonce);

      // Add form fields
      formDataSubmit.append(
        "voltage",
        formData.electrical_specifications.voltage || ""
      );
      formDataSubmit.append(
        "amperage",
        formData.electrical_specifications.amperage || ""
      );
      formDataSubmit.append(
        "distance_range_km",
        formData.electrical_specifications.distance_range_km || 0
      );
      formDataSubmit.append(
        "height_cm",
        formData.physical_dimensions.height_cm || 0
      );
      formDataSubmit.append(
        "width_cm",
        formData.physical_dimensions.width_cm || 0
      );
      formDataSubmit.append(
        "length_cm",
        formData.physical_dimensions.length_cm || 0
      );
      formDataSubmit.append(
        "cantidad_motores",
        formData.cantidad_motores || 1
      );
      formDataSubmit.append("scooter_model", formData.scooter_model || "");
      formDataSubmit.append(
        "battery_location",
        formData.battery_location || ""
      );
      formDataSubmit.append("connector_type", formData.connector_type || "");
      formDataSubmit.append("product_id", productId || 0);
      formDataSubmit.append("battery_type", batteryType);

      // DEBUG: log what we're sending so we can diagnose variation lookup failures
      console.log("DEBUG AJAX payload:", {
        action: "custom_batery_add_to_cart",
        product_id: productId,
        battery_type: batteryType,
        voltage: formData.electrical_specifications.voltage,
        amperage: formData.electrical_specifications.amperage,
        variation_id: formData.variation_id,
        nonce_present: !!nonce,
      });

      // Submit to WordPress AJAX endpoint
      const response = await fetch(ajaxUrl, {
        method: "POST",
        credentials: "include",
        body: formDataSubmit,
      });

      if (!response.ok) {
        throw new Error(`Network Error: ${response.status}`);
      }

      const result = await response.json();
      console.log("AJAX Response:", result);

      if (!result.success) {
        console.error("AJAX Error response data:", result.data);
        throw new Error(result.data?.message || "Error al añadir al carrito");
      }

      return result.data;
    } catch (error) {
      console.error("Add to Cart Error:", error);
      throw error;
    }
  },
};

/**
 * Main Form Controller
 * Coordinates all modules for form handling
 */
export const formController = () => {
  const form = document.querySelector(".sabway-form");
  const submitButton = document.getElementById("sab-submit-button");

  if (!form || !submitButton) {
    console.warn("Sabway form or submit button not found");
    return;
  }

  /**
   * Main form submission handler
   * @param {Event} e - Form submit event
   */
  const handleSubmit = async (e) => {
    e.preventDefault();

    // Step 1: Validate form using validator module
    const validation = formValidator.validate();
    if (!validation.isValid) {
      uiManager.showFeedback(
        `Por favor, corrige los siguientes errores:\n${validation.errors.join("\n")}`,
        "error",
        form
      );
      return;
    }

    // Step 2: Set loading state
    uiManager.setLoadingState(submitButton, true);

    try {
      // Step 3: Collect form data
      const formData = dataCollector.collect();
      console.log("Form Data Collected:", formData);

      // Step 4: Add to Cart for all users
      const result = await ajaxSubmitter.addToCart(formData);
      console.log("Added to Cart Successfully:", result);

      uiManager.showFeedback(
        "¡Producto añadido al carrito! Redirigiendo...",
        "success",
        form
      );

      // Step 5: Redirect to cart
      if (result.cart_url) {
        setTimeout(() => {
          window.location.href = result.cart_url;
        }, 1500);
      }
    } catch (error) {
      console.error("Form Submission Error:", error);
      uiManager.showFeedback(
        `Error al enviar el pedido: ${error.message}. Por favor, intenta de nuevo.`,
        "error",
        form
      );
    } finally {
      uiManager.setLoadingState(submitButton, false);
    }
  };

  // Step 9: Attach event listeners
  submitButton.addEventListener("click", handleSubmit);
  form.addEventListener("submit", handleSubmit);

  // Step 9.5: Handle custom connector field visibility
  const handleConnectorChange = () => {
    const connectorRadios = document.querySelectorAll('input[name="tipo-de-conector"]');
    const customConnectorContainer = document.getElementById("custom-connector-container");
    
    if (customConnectorContainer) {
      connectorRadios.forEach((radio) => {
        radio.addEventListener("change", () => {
          if (radio.value === 'OTROS') {
            customConnectorContainer.classList.remove("invisible");
            // Focus on the input field for better UX
            const customInput = document.getElementById("text-input-conector");
            if (customInput) {
              setTimeout(() => customInput.focus(), 100);
            }
          } else {
            customConnectorContainer.classList.add("invisible");
            // Clear the custom input when switching away from OTROS
            const customInput = document.getElementById("text-input-conector");
            if (customInput) {
              customInput.value = '';
            }
          }
          // Update summary when connector changes
          uiManager.updatesumary();
        });
      });
    }
  };
  
  handleConnectorChange();

  // Step 9.6: Handle battery location change to show/hide dimensions container
  const handleBatteryLocationChange = () => {
    const locationRadios = document.querySelectorAll('input[name="ubicacion-de-bateria"]');
    const dimensionsContainer = document.getElementById("dimensions-container");

    const applyLocationState = (value) => {
      if (!dimensionsContainer) return;
      const isInternal = value === 'Interna';
      if (isInternal) {
        dimensionsContainer.classList.remove("hidden");
      } else {
        dimensionsContainer.classList.add("hidden");
        // Clear dimension inputs when hiding
        const alto = document.getElementById("alto-bateria");
        const ancho = document.getElementById("ancho-bateria");
        const largo = document.getElementById("largo-bateria");
        if (alto) alto.value = '';
        if (ancho) ancho.value = '';
        if (largo) largo.value = '';
      }
      // Show/hide dimension rows in confirmation step
      uiManager.toggleConfirmationRow("altocm", isInternal);
      uiManager.toggleConfirmationRow("anchocm", isInternal);
      uiManager.toggleConfirmationRow("largocm", isInternal);
      uiManager.updatesumary();
    };

    locationRadios.forEach((radio) => {
      radio.addEventListener("change", () => applyLocationState(radio.value));
    });

    // Apply initial state on load
    const checkedRadio = document.querySelector('input[name="ubicacion-de-bateria"]:checked');
    if (checkedRadio) applyLocationState(checkedRadio.value);
  };

  handleBatteryLocationChange();

  // Step 10: Add real-time form summary updates
  const addSummaryListeners = () => {
    // Listen for changes on all form elements
    const formElements = form.querySelectorAll("input, select, textarea");
    formElements.forEach((element) => {
      // Add change event listener
      element.addEventListener("change", () => {
        uiManager.updatesumary();
      });

      // Add input event listener for real-time updates on text fields
      if (
        element.type === "text" ||
        element.type === "number" ||
        element.type === "range"
      ) {
        element.addEventListener("input", () => {
          uiManager.updatesumary();
        });
      }
    });
  };

  // Initialize summary updates
  addSummaryListeners();

  // NEW: Step 10.5: Initialize pricing matrix from AJAX
  const initializePricingMatrix = async () => {
    try {
      // Get AJAX configuration
      const ajaxConfig = window.taller_sabway_ajax || window.ecolitio_ajax || {};
      const ajaxUrl = ajaxConfig.ajax_url || `${window.location.origin}/wp-admin/admin-ajax.php`;
      
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'get_battery_pricing_matrix',
        }),
      });
      
      const result = await response.json();
      if (result.success) {
        window.batteryPricingMatrix = result.data;
        console.log('Pricing matrix loaded:', window.batteryPricingMatrix);
      } else {
        console.error('Error loading pricing matrix:', result.data);
      }
    } catch (error) {
      console.error('Error loading pricing matrix:', error);
    }
  };
  
  // Call on form initialization
  initializePricingMatrix();

  // NEW: Step 10.6: Handle voltage and amperage changes for dynamic pricing
  const handleVoltageAmperageChangeForPricing = () => {
    const voltageRadios = document.querySelectorAll('input[name="voltage"]');
    const amperageRadios = document.querySelectorAll('input[name="amperage"]');
    
    const updatePrice = () => {
      const voltage = document.querySelector('input[name="voltage"]:checked')?.value;
      const amperage = document.querySelector('input[name="amperage"]:checked')?.value;
      
      if (voltage && amperage) {
        uiManager.displayDynamicPrice(voltage, amperage);
        uiManager.updateVariationId(voltage, amperage);
      }
    };
    
    voltageRadios.forEach(radio => radio.addEventListener('change', updatePrice));
    amperageRadios.forEach(radio => radio.addEventListener('change', updatePrice));
  };
  
  handleVoltageAmperageChangeForPricing();

  const resetButton = document.getElementById("reset-form-button");
  if (resetButton) {
    resetButton.addEventListener("click", () => {
      form.reset();
      uiManager.updatesumary();
      const sabSwiper = swiperSab();
      sabSwiper.slideTo(0);
    });
  }

  // Also call summary update when navigating to confirmation step
  const step4 = document.getElementById("sab-step-4");
  if (step4) {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (
          mutation.type === "attributes" &&
          mutation.attributeName === "style"
        ) {
          if (step4.style.display !== "none" && step4.style.display !== "") {
            uiManager.updatesumary();
          }
        }
      });
    });
    observer.observe(step4, { attributes: true });
  }

  console.log("Sabway form controller initialized");
};
