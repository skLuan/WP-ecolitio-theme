export const formController = () => {
  const form = document.querySelector('.sabway-form');
  const submitButton = document.getElementById('sab-submit-button');
  
  if (!form || !submitButton) {
    console.warn('Sabway form or submit button not found');
    return;
  }

  /**
   * Validates all form fields
   * @returns {Object} Validation result with isValid flag and errors array
   */
  const validateForm = () => {
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
  };

  /**
   * Collects all form field values
   * @returns {Object} Form data object
   */
  const collectFormData = () => {
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
  };

  /**
   * Shows user feedback message
   * @param {string} message - Message to display
   * @param {string} type - Message type: 'success', 'error', 'info'
   */
  const showFeedback = (message, type = 'info') => {
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

    form.insertBefore(feedbackDiv, form.firstChild);

    // Auto-remove success messages after 5 seconds
    if (type === 'success') {
      setTimeout(() => {
        feedbackDiv.remove();
      }, 5000);
    }
  };

  /**
   * Sets loading state on submit button
   * @param {boolean} isLoading - Loading state
   */
  const setLoadingState = (isLoading) => {
    if (isLoading) {
      submitButton.disabled = true;
      submitButton.classList.add('opacity-50', 'cursor-not-allowed');
      submitButton.innerHTML = '<iconify-icon icon="eos-icons:loading" class="!align-middle !mr-2" width="16" height="16"></iconify-icon>Enviando...';
    } else {
      submitButton.disabled = false;
      submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
      submitButton.innerHTML = 'Finalizar Pedido';
    }
  };

  /**
   * Constructs comprehensive order object for WooCommerce
   * @param {Object} formData - Collected form data
   * @returns {Object} Order object for WooCommerce REST API
   */
  const constructOrderObject = (formData) => {
    // Get product ID from the page
    const productElement = document.querySelector('[id^="product-"]');
    const productId = productElement ? parseInt(productElement.id.replace('product-', '')) : null;

    // Get current user info (if logged in)
    const userEmail = document.querySelector('input[name="billing_email"]')?.value || 'sabway@company.com';
    const userName = document.querySelector('input[name="billing_first_name"]')?.value || 'Sabway Company';

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

    // Construct the order object
    const orderObject = {
      payment_method: 'bacs', // Bank transfer (BACS) as default
      payment_method_title: 'Direct Bank Transfer',
      set_paid: false, // Sabway will handle payment consolidation
      status: 'pending', // Order starts as pending
      customer_note: `Sabway Battery Customization Order\n\nElectrical Specifications:\n- Voltage: ${formData.electrical_specifications.voltage}\n- Amperage: ${formData.electrical_specifications.amperage}\n- Distance Range: ${formData.electrical_specifications.distance_range_km}km\n\nPhysical Dimensions:\n- Height: ${formData.physical_dimensions.height_cm}cm\n- Width: ${formData.physical_dimensions.width_cm}cm\n- Length: ${formData.physical_dimensions.length_cm}cm\n\nScooter Model: ${formData.scooter_model}\nBattery Location: ${formData.battery_location}\nConnector Type: ${formData.connector_type}`,
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
        }
      ],
      billing: {
        first_name: 'Sabway',
        last_name: 'Company',
        company: 'Sabway',
        email: userEmail,
        phone: '',
        address_1: '',
        address_2: '',
        city: '',
        state: '',
        postcode: '',
        country: ''
      },
      shipping: {
        first_name: 'Sabway',
        last_name: 'Company',
        company: 'Sabway',
        address_1: '',
        address_2: '',
        city: '',
        state: '',
        postcode: '',
        country: ''
      }
    };

    return orderObject;
  };

  /**
   * Submits order to WooCommerce REST API
   * @param {Object} formData - Form data object
   * @param {Object} orderObject - Order object to submit
   * @returns {Promise} API response
   */
  const submitOrderToAPI = async (formData, orderObject) => {
    try {
      // Use WooCommerce REST API endpoint
      const apiUrl = `${window.location.origin}/wp-json/wc/v3/orders`;
      
      // Get current user info for authentication
      const userEmail = document.querySelector('input[name="billing_email"]')?.value || 'sabway@company.com';
      const userName = document.querySelector('input[name="billing_first_name"]')?.value || 'Sabway Company';

      // Get REST API nonce from localized script (proper WooCommerce REST API nonce)
      const wcRestNonce = window.ecolitioWcApi?.restNonce ||
                         document.querySelector('input[name="wc-ajax-cart-update"]')?.value ||
                         'ecolitio_wc_rest_nonce';

      console.log('Using REST API nonce:', wcRestNonce);
      console.log('ecolitioWcApi object:', window.ecolitioWcApi);

      // Construct order data according to WooCommerce REST API format
      const orderData = {
        payment_method: 'bacs',
        payment_method_title: 'Direct Bank Transfer',
        set_paid: false,
        status: 'pending',
        billing: {
          first_name: 'Sabway',
          last_name: 'Company',
          company: 'Sabway',
          email: userEmail,
          phone: '',
          address_1: '',
          address_2: '',
          city: '',
          state: '',
          postcode: '',
          country: ''
        },
        shipping: {
          first_name: 'Sabway',
          last_name: 'Company',
          company: 'Sabway',
          address_1: '',
          address_2: '',
          city: '',
          state: '',
          postcode: '',
          country: ''
        },
        line_items: [
          {
            product_id: orderObject.line_items[0]?.product_id || null,
            quantity: 1,
            meta_data: [
              {
                key: 'voltage',
                value: formData.electrical_specifications.voltage || ''
              },
              {
                key: 'amperage',
                value: formData.electrical_specifications.amperage || ''
              },
              {
                key: 'distance_range_km',
                value: formData.electrical_specifications.distance_range_km || ''
              },
              {
                key: 'alto',
                value: formData.physical_dimensions.height_cm || ''
              },
              {
                key: 'ancho',
                value: formData.physical_dimensions.width_cm || ''
              },
              {
                key: 'largo',
                value: formData.physical_dimensions.length_cm || ''
              },
              {
                key: 'scooter_model',
                value: formData.scooter_model || ''
              },
              {
                key: 'battery_location',
                value: formData.battery_location || ''
              },
              {
                key: 'connector_type',
                value: formData.connector_type || ''
              }
            ]
          }
        ],
        meta_data: [
          {
            key: '_sabway_order_type',
            value: 'battery_customization'
          },
          {
            key: '_company_order',
            value: 'true'
          }
        ]
      };

      // Prepare headers with proper REST API authentication
      const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-WP-Nonce': wcRestNonce // Use the proper REST API nonce
      };

      console.log('Sending request with headers:', headers);
      console.log('Order data:', orderData);

      const response = await fetch(apiUrl, {
        method: 'POST',
        headers: headers,
        credentials: 'include', // Include cookies for session-based authentication
        body: JSON.stringify(orderData)
      });

      if (!response.ok) {
        const errorText = await response.text();
        console.error('API Error Response:', {
          status: response.status,
          statusText: response.statusText,
          body: errorText
        });
        
        // Provide more specific error messages based on status code
        if (response.status === 403) {
          throw new Error(`Authentication Error (403): Cookie check failed. Nonce: ${wcRestNonce}. Please check if you're logged in and have proper permissions.`);
        } else if (response.status === 401) {
          throw new Error(`Authorization Error (401): Insufficient permissions. Check user role capabilities.`);
        } else {
          throw new Error(`WooCommerce API Error: ${response.status} - ${errorText}`);
        }
      }

      const result = await response.json();
      console.log('Order created successfully:', result);
      
      return {
        id: result.id,
        order_key: result.order_key,
        redirect_url: result._links?.checkout?.href || `${window.location.origin}/checkout/order-received/${result.id}/?key=${result.order_key}`
      };
    } catch (error) {
      console.error('WooCommerce API Submission Error:', error);
      throw error;
    }
  };

  /**
   * Handles form submission
   */
  const handleSubmit = async (e) => {
    e.preventDefault();

    // Validate form
    const validation = validateForm();
    if (!validation.isValid) {
      showFeedback(
        `Please fix the following errors:\n${validation.errors.join('\n')}`,
        'error'
      );
      return;
    }

    // Set loading state
    setLoadingState(true);

    try {
      // Collect form data
      const formData = collectFormData();
      console.log('Form Data Collected:', formData);

      // Construct order object
      const orderObject = constructOrderObject(formData);
      console.log('Order Object Constructed:', orderObject);

      // Submit to API with both formData and orderObject
      const result = await submitOrderToAPI(formData, orderObject);
      console.log('Order Created Successfully:', result);

      // Show success message
      showFeedback(
        `¡Pedido realizado con éxito! Order ID: ${result.id}`,
        'success'
      );

      // Navigate to confirmation step (step 5)
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

      // Reset form after successful submission
      setTimeout(() => {
        form.reset();
      }, 2000);

    } catch (error) {
      console.error('Form Submission Error:', error);
      showFeedback(
        `Error al enviar el pedido: ${error.message}. Por favor, intente de nuevo.`,
        'error'
      );
    } finally {
      setLoadingState(false);
    }
  };

  /**
   * Populates confirmation step with order details
   * @param {Object} formData - Form data
   * @param {number} orderId - Created order ID
   */
  const populateConfirmation = (formData, orderId) => {
    const confirmationItems = [
      { id: 'Voltios', value: formData.electrical_specifications.voltage },
      { id: 'Amperios', value: formData.electrical_specifications.amperage },
      { id: 'Autonomía', value: `${formData.electrical_specifications.distance_range_km}km` },
      { id: 'Alto', value: `${formData.physical_dimensions.height_cm}cm` },
      { id: 'Ancho', value: `${formData.physical_dimensions.width_cm}cm` },
      { id: 'Largo', value: `${formData.physical_dimensions.length_cm}cm` },
      { id: 'Modelo', value: formData.scooter_model },
      { id: 'Ubicación', value: formData.battery_location },
      { id: 'Conector', value: formData.connector_type }
    ];

    confirmationItems.forEach(item => {
      const element = document.getElementById(`final-check-${item.id}`);
      if (element) {
        const pElement = element.querySelector('p');
        if (pElement) {
          pElement.textContent = item.value;
        }
      }
    });

    // Add order ID to confirmation
    const orderIdElement = document.createElement('li');
    orderIdElement.className = 'block';
    orderIdElement.innerHTML = `<strong>Order ID:</strong><p>${orderId}</p>`;
    const confirmationList = document.querySelector('#sab-step-5 ul');
    if (confirmationList) {
      confirmationList.appendChild(orderIdElement);
    }
  };

  // Attach submit handler to button
  submitButton.addEventListener('click', handleSubmit);

  // Also handle form submission if form is submitted directly
  form.addEventListener('submit', handleSubmit);

  console.log('Sabway form controller initialized');
};
