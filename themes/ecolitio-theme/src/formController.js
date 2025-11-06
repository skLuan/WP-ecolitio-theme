import { nextSlide } from "./main";

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
    const distanceRange = document.getElementById('sab-distance-range');
    if (!distanceRange || !distanceRange.value) {
      errors.push('Distance range is required');
    }

    // Validate voltage selection
    const voltageSelected = document.querySelector('input[name="voltage"]:checked');
    if (!voltageSelected) {
      errors.push('Voltage specification is required');
    }

    // Validate amperage selection
    const amperageSelected = document.querySelector('input[name="amperage"]:checked');
    if (!amperageSelected) {
      errors.push('Amperage specification is required');
    }

    // Validate battery location
    const locationSelected = document.querySelector('input[name="ubicacion-de-bateria"]:checked');
    if (!locationSelected) {
      errors.push('Battery location is required');
    }

    // Validate physical dimensions
    const height = document.getElementById('alto-bateria');
    const width = document.getElementById('ancho-bateria');
    const length = document.getElementById('largo-bateria');

    if (!height || !height.value || isNaN(height.value) || parseFloat(height.value) <= 0) {
      errors.push('Valid height (cm) is required');
    }

    if (!width || !width.value || isNaN(width.value) || parseFloat(width.value) <= 0) {
      errors.push('Valid width (cm) is required');
    }

    if (!length || !length.value || isNaN(length.value) || parseFloat(length.value) <= 0) {
      errors.push('Valid length (cm) is required');
    }

    // Validate scooter model
    const scooterModel = document.getElementById('modelo-patinete');
    if (!scooterModel || !scooterModel.value.trim()) {
      errors.push('Scooter model is required');
    }

    // Validate connector type
    const connectorSelected = document.querySelector('input[name="tipo-de-conector"]:checked');
    if (!connectorSelected) {
      errors.push('Connector type is required');
    }

    return {
      isValid: errors.length === 0,
      errors
    };
  }
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
    const distanceRange = document.getElementById('sab-distance-range');
    const voltageSelected = document.querySelector('input[name="voltage"]:checked');
    const amperageSelected = document.querySelector('input[name="amperage"]:checked');
    const locationSelected = document.querySelector('input[name="ubicacion-de-bateria"]:checked');
    const height = document.getElementById('alto-bateria');
    const width = document.getElementById('ancho-bateria');
    const length = document.getElementById('largo-bateria');
    const scooterModel = document.getElementById('modelo-patinete');
    const connectorSelected = document.querySelector('input[name="tipo-de-conector"]:checked');

    return {
      electrical_specifications: {
        voltage: voltageSelected ? voltageSelected.value : null,
        amperage: amperageSelected ? amperageSelected.value : null,
        distance_range_km: distanceRange ? parseInt(distanceRange.value) : null
      },
      physical_dimensions: {
        height_cm: height ? parseFloat(height.value) : null,
        width_cm: width ? parseFloat(width.value) : null,
        length_cm: length ? parseFloat(length.value) : null
      },
      scooter_model: scooterModel ? scooterModel.value.trim() : null,
      battery_location: locationSelected ? locationSelected.value : null,
      connector_type: connectorSelected ? connectorSelected.value : null
    };
  }
};

/**
 * UI Management Module
 * Handles all UI interactions and feedback
 */
const uiManager = {
  /**
   * Shows user feedback message
   * @param {string} message - Message to display
   * @param {string} type - Message type: 'success', 'error', 'info'
   * @param {HTMLElement} formElement - Form element to attach feedback to
   */
  showFeedback(message, type = 'info', formElement) {
    // Remove existing feedback if present
    const existingFeedback = document.querySelector('.sabway-form-feedback');
    if (existingFeedback) {
      existingFeedback.remove();
    }

    const feedbackDiv = document.createElement('div');
    feedbackDiv.className = `sabway-form-feedback !p-4 !rounded-lg !mb-4 !text-white-eco ${
      type === 'success' ? '!bg-green-eco !text-black-eco' :
      type === 'error' ? '!bg-red-500' :
      '!bg-blue-eco'
    }`;
    feedbackDiv.textContent = message;

    formElement.insertBefore(feedbackDiv, formElement.firstChild);

    // Auto-remove success messages after 5 seconds
    if (type === 'success') {
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
      submitButton.classList.add('opacity-50', 'cursor-not-allowed');
      submitButton.innerHTML = '<iconify-icon icon="eos-icons:loading" class="!align-middle !mr-2" width="16" height="16"></iconify-icon>Enviando...';
    } else {
      submitButton.disabled = false;
      submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
      submitButton.innerHTML = 'Finalizar Pedido';
    }
  },
  updatesumary() {
    // Collect current form data
    const formData = {
      voltage: document.querySelector('input[name="voltage"]:checked')?.value || 'No seleccionado',
      amperage: document.querySelector('input[name="amperage"]:checked')?.value || 'No seleccionado',
      distanceRange: document.getElementById('sab-distance-range')?.value || '0',
      height: document.getElementById('alto-bateria')?.value || '0',
      width: document.getElementById('ancho-bateria')?.value || '0',
      length: document.getElementById('largo-bateria')?.value || '0',
      scooterModel: document.getElementById('modelo-patinete')?.value || 'No especificado',
      batteryLocation: document.querySelector('input[name="ubicacion-de-bateria"]:checked')?.value || 'No seleccionado',
      connectorType: document.querySelector('input[name="tipo-de-conector"]:checked')?.value || 'No seleccionado'
    };

    // Update confirmation fields with current values
    this.updateConfirmationField('voltios', formData.voltage);
    this.updateConfirmationField('amperios', formData.amperage);
    this.updateConfirmationField('autonomia', `${formData.distanceRange}km`);
    this.updateConfirmationField('altocm', `${formData.height}cm`);
    this.updateConfirmationField('anchocm', `${formData.width}cm`);
    this.updateConfirmationField('largocm', `${formData.length}cm`);
    this.updateConfirmationField('modelo-de-patinete-elctrico', formData.scooterModel);
    this.updateConfirmationField('ubicacin-de-bateria', formData.batteryLocation);
    this.updateConfirmationField('tipo-de-conector', formData.connectorType);
  },
  /**
   * Updates a specific confirmation field
   * @param {string} fieldName - The field name to update
   * @param {string} value - The value to display
   */
  updateConfirmationField(fieldName, value) {
    const confirmationElement = document.getElementById(`final-check-${fieldName}`);
    if (confirmationElement) {
      const valueElement = confirmationElement.querySelector('p');
      if (valueElement) {
        valueElement.textContent = value;
      }
    }
  }
};

/**
 * Order Construction Module
 * Handles creating order objects for submission
 */
const orderConstructor = {
  /**
   * Constructs comprehensive order object for WooCommerce
   * @param {Object} formData - Collected form data
   * @returns {Object} Order object for WooCommerce
   */
  construct(formData) {
    // Get product ID from the page
    const productElement = document.querySelector('[id^="product-"]');
    const productId = productElement ? parseInt(productElement.id.replace('product-', '')) : null;

    // Get current user info (if logged in)
    const userEmail = document.querySelector('input[name="billing_email"]')?.value || 'sabway@company.com';
    const userName = document.querySelector('input[name="billing_first_name"]')?.value || 'Sabway Company';
    const userLastName = document.querySelector('input[name="billing_last_name"]')?.value || 'Company';
    const userCompany = document.querySelector('input[name="billing_company"]')?.value || 'Sabway';
    const userPhone = document.querySelector('input[name="billing_phone"]')?.value || '';
    const userAddress1 = document.querySelector('input[name="billing_address_1"]')?.value || '';
    const userAddress2 = document.querySelector('input[name="billing_address_2"]')?.value || '';
    const userCity = document.querySelector('input[name="billing_city"]')?.value || '';
    const userState = document.querySelector('input[name="billing_state"]')?.value || '';
    const userPostcode = document.querySelector('input[name="billing_postcode"]')?.value || '';
    const userCountry = document.querySelector('input[name="billing_country"]')?.value || '';

    // Construct order meta data with all specifications
    const orderMeta = {
      electrical_specifications: formData.electrical_specifications,
      physical_dimensions: formData.physical_dimensions,
      scooter_model: formData.scooter_model,
      battery_location: formData.battery_location,
      connector_type: formData.connector_type,
      order_type: 'sabway_battery_customization',
      company_order: true
    };

    // Construct order note with form resume for internal staff
    const orderNote = `SABWAY BATTERY CUSTOMIZATION ORDER RESUME\n\n` +
      `ELECTRICAL SPECIFICATIONS:\n` +
      `- Voltage: ${formData.electrical_specifications.voltage}\n` +
      `- Amperage: ${formData.electrical_specifications.amperage}\n` +
      `- Distance Range: ${formData.electrical_specifications.distance_range_km}km\n\n` +
      `PHYSICAL DIMENSIONS:\n` +
      `- Height: ${formData.physical_dimensions.height_cm}cm\n` +
      `- Width: ${formData.physical_dimensions.width_cm}cm\n` +
      `- Length: ${formData.physical_dimensions.length_cm}cm\n\n` +
      `SCOOTER SPECIFICATIONS:\n` +
      `- Model: ${formData.scooter_model}\n` +
      `- Battery Location: ${formData.battery_location}\n` +
      `- Connector Type: ${formData.connector_type}\n\n` +
      `ORDER SOURCE: Sabway Space (sabway-space)`;

    // Construct the order object
    const orderObject = {
      payment_method: 'bacs', // Bank transfer (BACS) as default
      payment_method_title: 'Direct Bank Transfer',
      set_paid: false, // Sabway will handle payment consolidation
      status: 'pending', // Order starts as pending
      customer_note: `Sabway Battery Customization Order\n\nElectrical Specifications:\n- Voltage: ${formData.electrical_specifications.voltage}\n- Amperage: ${formData.electrical_specifications.amperage}\n- Distance Range: ${formData.electrical_specifications.distance_range_km}km\n\nPhysical Dimensions:\n- Height: ${formData.physical_dimensions.height_cm}cm\n- Width: ${formData.physical_dimensions.width_cm}cm\n- Length: ${formData.physical_dimensions.length_cm}cm\n\nScooter Model: ${formData.scooter_model}\nBattery Location: ${formData.battery_location}\nConnector Type: ${formData.connector_type}`,
      order_note: orderNote,
      line_items: [
        {
          product_id: productId,
          quantity: 1,
          meta_data: [
            {
              key: 'battery_specifications',
              value: JSON.stringify(orderMeta)
            }
          ]
        }
      ],
      meta_data: [
        {
          key: '_sabway_battery_specs',
          value: JSON.stringify(orderMeta)
        },
        {
          key: '_company_order',
          value: 'true'
        },
        {
          key: '_wc_order_origin',
          value: 'sabway-space'
        }
      ],
      billing: {
        first_name: userName,
        last_name: userLastName,
        company: userCompany,
        email: userEmail,
        phone: userPhone,
        address_1: userAddress1,
        address_2: userAddress2,
        city: userCity,
        state: userState,
        postcode: userPostcode,
        country: userCountry
      },
      shipping: {
        first_name: userName,
        last_name: userLastName,
        company: userCompany,
        address_1: userAddress1,
        address_2: userAddress2,
        city: userCity,
        state: userState,
        postcode: userPostcode,
        country: userCountry
      }
    };

    return orderObject;
  }
};

/**
 * AJAX Submission Module
 * Handles order submission to WordPress AJAX endpoint
 */
const ajaxSubmitter = {
  /**
   * Submits order to WordPress AJAX endpoint with enhanced security
   * @param {Object} formData - Form data object
   * @param {Object} orderObject - Order object to submit
   * @returns {Promise} API response
   */
  async submit(formData, orderObject) {
    try {
      // Get AJAX configuration from localized script
      const ajaxConfig = window.taller_sabway_ajax || window.ecolitio_ajax || {};
      const ajaxUrl = ajaxConfig.ajax_url || `${window.location.origin}/wp-admin/admin-ajax.php`;
      const nonce = ajaxConfig.sabway_form_nonce || ajaxConfig.nonce || '';
      
      console.log('Using WordPress AJAX submission');
      console.log('AJAX URL:', ajaxUrl);
      console.log('Nonce:', nonce ? 'Present' : 'Missing');
      console.log('AJAX Config:', ajaxConfig);

      // Get product ID from order object
      const productId = orderObject.line_items[0]?.product_id || null;

      // Prepare FormData for WordPress AJAX submission
      const formDataSubmit = new FormData();
      formDataSubmit.append('action', 'sabway_submit_form');
      formDataSubmit.append('nonce', nonce);
      
      // Add form fields according to AJAX handler expectations
      formDataSubmit.append('voltage', formData.electrical_specifications.voltage || '');
      formDataSubmit.append('amperage', formData.electrical_specifications.amperage || '');
      formDataSubmit.append('distance_range_km', formData.electrical_specifications.distance_range_km || 0);
      formDataSubmit.append('height_cm', formData.physical_dimensions.height_cm || 0);
      formDataSubmit.append('width_cm', formData.physical_dimensions.width_cm || 0);
      formDataSubmit.append('length_cm', formData.physical_dimensions.length_cm || 0);
      formDataSubmit.append('scooter_model', formData.scooter_model || '');
      formDataSubmit.append('battery_location', formData.battery_location || '');
      formDataSubmit.append('connector_type', formData.connector_type || '');
      formDataSubmit.append('product_id', productId || 0);

      console.log('Form data prepared for submission:', Object.fromEntries(formDataSubmit));

      // Submit to WordPress AJAX endpoint
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'include', // Include cookies for authentication
        body: formDataSubmit
      });

      if (!response.ok) {
        const errorText = await response.text();
        console.error('AJAX Error Response:', {
          status: response.status,
          statusText: response.statusText,
          body: errorText
        });
        throw new Error(`Network Error: ${response.status} - ${errorText}`);
      }

      const result = await response.json();
      console.log('AJAX Response:', result);
      
      if (!result.success) {
        // Handle different types of errors from the AJAX handler
        const errorData = result.data || {};
        const errorMessage = errorData.message || 'Error desconocido';
        const errorCode = errorData.code || 'unknown_error';
        
        console.error('AJAX Submission Error:', {
          code: errorCode,
          message: errorMessage,
          details: errorData
        });
        
        // Provide user-friendly error messages based on error code
        if (errorCode === 'nonce_failed') {
          throw new Error('Verificación de seguridad fallida. Por favor, recarga la página e intenta de nuevo.');
        } else if (errorCode === 'session_failed') {
          throw new Error('Sesión inválida o expirada. Por favor, inicia sesión nuevamente.');
        } else if (errorCode === 'permission_failed') {
          throw new Error('No tienes permisos para realizar esta acción.');
        } else if (errorCode === 'validation_failed') {
          const validationErrors = errorData.errors || [];
          throw new Error(`Datos del formulario inválidos:\n${validationErrors.join('\n')}`);
        } else if (errorCode === 'product_unavailable') {
          throw new Error('El producto seleccionado no está disponible.');
        } else {
          throw new Error(errorMessage);
        }
      }
      nextSlide();
      // Success response from AJAX handler
      const successData = result.data || {};
      console.log('Order created successfully via AJAX:', successData);
      
      return {
        id: successData.order_id,
        order_key: successData.order_key,
        redirect_url: successData.redirect_url || `${window.location.origin}/checkout/order-received/${successData.order_id}/?key=${successData.order_key}`,
        message: successData.message || 'Pedido realizado con éxito'
      };
    } catch (error) {
      console.error('AJAX Submission Error:', error);
      throw error;
    }
  }
};

/**
 * Main Form Controller
 * Coordinates all modules for form handling
 */
export const formController = () => {
  const form = document.querySelector('.sabway-form');
  const submitButton = document.getElementById('sab-submit-button');
  
  if (!form || !submitButton) {
    console.warn('Sabway form or submit button not found');
    return;
  }

  /**
   * Populates confirmation step with order details
   * @param {Object} formData - Form data
   * @param {number} orderId - Created order ID
   */
  const populateConfirmation = (formData, orderId) => {
    // Add order ID to confirmation
    const orderIdElement = document.createElement('li');
    orderIdElement.className = 'block';
    orderIdElement.innerHTML = `<strong>Order ID:</strong><p>${orderId}</p>`;
    const confirmationList = document.querySelector('#sab-step-5 ul');
    if (confirmationList) {
      confirmationList.appendChild(orderIdElement);
    }
  };

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
        `Please fix the following errors:\n${validation.errors.join('\n')}`,
        'error',
        form
      );
      return;
    }

    // Step 2: Set loading state
    uiManager.setLoadingState(submitButton, true);

    try {
      // Step 3: Collect form data
      const formData = dataCollector.collect();
      console.log('Form Data Collected:', formData);

      // Step 4: Construct order object
      const orderObject = orderConstructor.construct(formData);
      console.log('Order Object Constructed:', orderObject);

      // Step 5: Submit via AJAX
      const result = await ajaxSubmitter.submit(formData, orderObject);
      console.log('Order Created Successfully:', result);

      // Step 6: Show success feedback
      uiManager.showFeedback(
        `¡Pedido realizado con éxito! Order ID: ${result.id}`,
        'success',
        form
      );

      // Step 7: Navigate to confirmation step (step 5)
      const step5 = document.getElementById('sab-step-5');
      if (step5) {
        // Hide all steps
        document.querySelectorAll('.step').forEach(step => {
          step.style.display = 'none';
        });
        // Show success step
        step5.style.display = 'flex';
        
        // Populate final confirmation with order details
        populateConfirmation(formData, result.id);
      }

      // Step 8: Reset form after successful submission
      setTimeout(() => {
        form.reset();
      }, 2000);

    } catch (error) {
      console.error('Form Submission Error:', error);
      uiManager.showFeedback(
        `Error al enviar el pedido: ${error.message}. Por favor, intente de nuevo.`,
        'error',
        form
      );
    } finally {
      uiManager.setLoadingState(submitButton, false);
    }
  };

  // Step 9: Attach event listeners
  submitButton.addEventListener('click', handleSubmit);
  form.addEventListener('submit', handleSubmit);

  // Step 10: Add real-time form summary updates
  const addSummaryListeners = () => {
    // Listen for changes on all form elements
    const formElements = form.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
      // Add change event listener
      element.addEventListener('change', () => {
        uiManager.updatesumary();
      });
      
      // Add input event listener for real-time updates on text fields
      if (element.type === 'text' || element.type === 'number' || element.type === 'range') {
        element.addEventListener('input', () => {
          uiManager.updatesumary();
        });
      }
    });
  };

  // Initialize summary updates
  addSummaryListeners();
  
  // Also call summary update when navigating to confirmation step
  const step4 = document.getElementById('sab-step-4');
  if (step4) {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
          if (step4.style.display !== 'none' && step4.style.display !== '') {
            uiManager.updatesumary();
          }
        }
      });
    });
    observer.observe(step4, { attributes: true });
  }

  console.log('Sabway form controller initialized');
};
