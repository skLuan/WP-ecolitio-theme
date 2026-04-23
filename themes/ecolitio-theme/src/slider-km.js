/**
 * Battery Distance Range Synchronization Module
 * Handles synchronization where voltage is selected via buttons, and slider controls amperage selection
 * Maps battery specifications (voltage + amperage) to distance ranges
 */

/**
 * Distance lookup table mapping voltage + amperage combinations to distance ranges
 * Format: "voltage-amperage" => { min: km, max: km, midpoint: km }
 * Auto-generated from generate_distance_table.js
 * Supports both new pattern (5AH, 10AH, etc.) and old pattern (4,8AH, 9,6AH, etc.)
 */
const distanceLookupTable = {
  // 12V Battery - New Pattern (5AH, 10AH, 15AH, ...)
  "12V-5AH": { min: 2, max: 2, midpoint: 2 },
  "12V-10AH": { min: 4, max: 6, midpoint: 5 },
  "12V-15AH": { min: 6, max: 8, midpoint: 7 },
  "12V-20AH": { min: 8, max: 12, midpoint: 10 },
  "12V-25AH": { min: 10, max: 14, midpoint: 12 },
  "12V-30AH": { min: 12, max: 17, midpoint: 14 },
  "12V-35AH": { min: 14, max: 20, midpoint: 17 },
  "12V-40AH": { min: 16, max: 23, midpoint: 19 },

  // 24V Battery - New Pattern (5AH, 10AH, 15AH, ...)
  "24V-5AH": { min: 7, max: 10, midpoint: 8 },
  "24V-10AH": { min: 14, max: 20, midpoint: 17 },
  "24V-15AH": { min: 21, max: 30, midpoint: 25 },
  "24V-20AH": { min: 28, max: 41, midpoint: 34 },
  "24V-25AH": { min: 35, max: 50, midpoint: 42 },
  "24V-30AH": { min: 42, max: 60, midpoint: 50 },
  "24V-35AH": { min: 49, max: 71, midpoint: 59 },
  "24V-40AH": { min: 56, max: 80, midpoint: 67 },

  // 36V Battery - New Pattern (5AH, 10AH, 15AH, ...)
  "36V-5AH": { min: 13, max: 19, midpoint: 16 },
  "36V-10AH": { min: 27, max: 38, midpoint: 32 },
  "36V-15AH": { min: 41, max: 59, midpoint: 49 },
  "36V-20AH": { min: 54, max: 78, midpoint: 65 },
  "36V-25AH": { min: 67, max: 97, midpoint: 81 },
  "36V-30AH": { min: 81, max: 116, midpoint: 97 },
  "36V-35AH": { min: 94, max: 136, midpoint: 113 },
  "36V-40AH": { min: 108, max: 156, midpoint: 130 },

  // 48V Battery - New Pattern (5AH, 10AH, 15AH, ...)
  "48V-5AH": { min: 20, max: 29, midpoint: 24 },
  "48V-10AH": { min: 40, max: 58, midpoint: 48 },
  "48V-15AH": { min: 60, max: 86, midpoint: 72 },
  "48V-20AH": { min: 80, max: 115, midpoint: 96 },
  "48V-25AH": { min: 100, max: 144, midpoint: 120 },
  "48V-30AH": { min: 120, max: 173, midpoint: 144 },
  "48V-35AH": { min: 139, max: 202, midpoint: 168 },
  "48V-40AH": { min: 159, max: 230, midpoint: 192 },

  // 52V Battery - New Pattern (5AH, 10AH, 15AH, ...)
  "52V-5AH": { min: 22, max: 32, midpoint: 27 },
  "52V-10AH": { min: 46, max: 66, midpoint: 55 },
  "52V-15AH": { min: 68, max: 98, midpoint: 82 },
  "52V-20AH": { min: 90, max: 131, midpoint: 109 },
  "52V-25AH": { min: 114, max: 164, midpoint: 137 },
  "52V-30AH": { min: 136, max: 197, midpoint: 164 },
  "52V-35AH": { min: 159, max: 229, midpoint: 191 },
  "52V-40AH": { min: 181, max: 262, midpoint: 218 },

  // 60V Battery - New Pattern (5AH, 10AH, 15AH, ...)
  "60V-5AH": { min: 27, max: 40, midpoint: 33 },
  "60V-10AH": { min: 55, max: 79, midpoint: 66 },
  "60V-15AH": { min: 82, max: 119, midpoint: 99 },
  "60V-20AH": { min: 110, max: 158, midpoint: 132 },
  "60V-25AH": { min: 137, max: 198, midpoint: 165 },
  "60V-30AH": { min: 164, max: 238, midpoint: 198 },
  "60V-35AH": { min: 192, max: 277, midpoint: 231 },
  "60V-40AH": { min: 219, max: 317, midpoint: 264 },

  // 12V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)
  "12V-4,8AH": { min: 2, max: 2, midpoint: 2 },
  "12V-9,6AH": { min: 4, max: 6, midpoint: 5 },
  "12V-14,4AH": { min: 6, max: 8, midpoint: 7 },
  "12V-19,2AH": { min: 7, max: 11, midpoint: 9 },
  "12V-24AH": { min: 10, max: 14, midpoint: 12 },
  "12V-28,8AH": { min: 12, max: 17, midpoint: 14 },
  "12V-33,6AH": { min: 13, max: 19, midpoint: 16 },
  "12V-38,4AH": { min: 15, max: 22, midpoint: 18 },

  // 24V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)
  "24V-4,8AH": { min: 7, max: 10, midpoint: 8 },
  "24V-9,6AH": { min: 13, max: 19, midpoint: 16 },
  "24V-14,4AH": { min: 20, max: 29, midpoint: 24 },
  "24V-19,2AH": { min: 27, max: 38, midpoint: 32 },
  "24V-24AH": { min: 33, max: 48, midpoint: 40 },
  "24V-28,8AH": { min: 40, max: 58, midpoint: 48 },
  "24V-33,6AH": { min: 46, max: 67, midpoint: 56 },
  "24V-38,4AH": { min: 54, max: 78, midpoint: 65 },

  // 36V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)
  "36V-4,8AH": { min: 13, max: 19, midpoint: 16 },
  "36V-9,6AH": { min: 26, max: 37, midpoint: 31 },
  "36V-14,4AH": { min: 39, max: 56, midpoint: 47 },
  "36V-19,2AH": { min: 51, max: 74, midpoint: 62 },
  "36V-24AH": { min: 65, max: 94, midpoint: 78 },
  "36V-28,8AH": { min: 77, max: 112, midpoint: 93 },
  "36V-33,6AH": { min: 90, max: 131, midpoint: 109 },
  "36V-38,4AH": { min: 103, max: 149, midpoint: 124 },

  // 48V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)
  "48V-4,8AH": { min: 19, max: 28, midpoint: 23 },
  "48V-9,6AH": { min: 38, max: 55, midpoint: 46 },
  "48V-14,4AH": { min: 57, max: 83, midpoint: 69 },
  "48V-19,2AH": { min: 76, max: 110, midpoint: 92 },
  "48V-24AH": { min: 95, max: 138, midpoint: 115 },
  "48V-28,8AH": { min: 115, max: 166, midpoint: 138 },
  "48V-33,6AH": { min: 134, max: 193, midpoint: 161 },
  "48V-38,4AH": { min: 153, max: 221, midpoint: 184 },

  // 52V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)
  "52V-4,8AH": { min: 22, max: 31, midpoint: 26 },
  "52V-9,6AH": { min: 43, max: 62, midpoint: 52 },
  "52V-14,4AH": { min: 66, max: 95, midpoint: 79 },
  "52V-19,2AH": { min: 87, max: 126, midpoint: 105 },
  "52V-24AH": { min: 109, max: 157, midpoint: 131 },
  "52V-28,8AH": { min: 130, max: 188, midpoint: 157 },
  "52V-33,6AH": { min: 152, max: 220, midpoint: 183 },
  "52V-38,4AH": { min: 174, max: 252, midpoint: 210 },

  // 60V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)
  "60V-4,8AH": { min: 27, max: 38, midpoint: 32 },
  "60V-9,6AH": { min: 52, max: 76, midpoint: 63 },
  "60V-14,4AH": { min: 79, max: 114, midpoint: 95 },
  "60V-19,2AH": { min: 105, max: 152, midpoint: 127 },
  "60V-24AH": { min: 131, max: 190, midpoint: 158 },
  "60V-28,8AH": { min: 158, max: 228, midpoint: 190 },
  "60V-33,6AH": { min: 184, max: 266, midpoint: 222 },
  "60V-38,4AH": { min: 210, max: 304, midpoint: 253 },
};

/**
 * Slider-KM Synchronization Module
 * Manages bidirectional updates between slider and voltage/amperage inputs
 */
const sliderKmSync = {
  // Flag to prevent infinite loops during synchronization
  isUpdating: false,

  /**
    * Initialize the synchronization module
    * Sets up event listeners for voltage, amperage, and slider changes
    */
  init() {
    const form = document.querySelector(".sabway-form");
    if (!form) {
      console.warn("Sabway form not found for slider-km synchronization");
      return;
    }

    // Diagnostic: Log battery type
    const batteryTypeInput = document.querySelector('input[name="battery_type"]');
    const batteryType = batteryTypeInput ? batteryTypeInput.value : 'unknown';
    console.log("Slider-KM Init: Battery type =", batteryType);

    // Get all relevant form elements
    const voltageInputs = form.querySelectorAll('input[name="voltage"]');
    const amperageInputs = form.querySelectorAll('input[name="amperage"]');
    const distanceSlider = form.querySelector("#sab-distance-range");

    console.log("Slider-KM Init: Found voltage inputs:", voltageInputs.length);
    console.log("Slider-KM Init: Found amperage inputs:", amperageInputs.length);
    console.log("Slider-KM Init: Found distance slider:", !!distanceSlider);

    if (!distanceSlider) {
      console.warn("Distance slider not found");
      return;
    }

    // Set slider limits based on the lookup table
    const maxKm = Math.max(
      ...Object.values(distanceLookupTable).map((d) => d.max)
    );
    const minKm = Math.min(
      ...Object.values(distanceLookupTable).map((d) => d.min)
    );
    distanceSlider.min = minKm;
    distanceSlider.max = maxKm;

    // Add event listeners for voltage changes
    voltageInputs.forEach((input) => {
      input.addEventListener("change", () => {
        this.onVoltageOrAmperageChange();
      });
    });

    // Add event listeners for amperage changes
    amperageInputs.forEach((input) => {
      input.addEventListener("change", () => {
        this.onVoltageOrAmperageChange();
      });
    });

    // Add event listener for slider changes
    distanceSlider.addEventListener("input", () => {
      this.onSliderChange();
    });

    console.log("Slider-KM synchronization initialized");
  },

  /**
    * Handle voltage or amperage selection change
    * Updates slider to match the selected battery specification
    */
  onVoltageOrAmperageChange() {
    // Prevent infinite loops during synchronization
    if (this.isUpdating) {
      return;
    }

    this.isUpdating = true;

    try {
      const form = document.querySelector(".sabway-form");
      const voltageSelected = form.querySelector(
        'input[name="voltage"]:checked'
      );
      const amperageSelected = form.querySelector(
        'input[name="amperage"]:checked'
      );
      const distanceSlider = form.querySelector("#sab-distance-range");

      // Only update if both voltage and amperage are selected
      if (!voltageSelected || !amperageSelected) {
        console.log("Slider-KM: Voltage or amperage not selected yet");
        return;
      }

      const voltage = voltageSelected.value;
      const amperage = amperageSelected.value;
      const key = `${voltage}-${amperage}`;

      console.log(`Slider-KM: onVoltageOrAmperageChange - key="${key}"`);
      console.log(`Slider-KM: Looking up key in distanceLookupTable...`);

      // Look up the distance range for this combination
      const distanceData = distanceLookupTable[key];
      if (distanceData) {
        // Set slider to the midpoint of the distance range
        distanceSlider.value = distanceData.midpoint;

        // Update the distance range display
        this.updateDistanceRangeDisplay(distanceData);

        // Trigger input event to update summary
        distanceSlider.dispatchEvent(new Event("input", { bubbles: true }));

        console.log(
          `Updated slider to ${distanceData.midpoint}km for ${key} (range: ${distanceData.min}-${distanceData.max}km)`
        );
      } else {
        console.warn(`Slider-KM: Key "${key}" NOT found in distanceLookupTable`);
        console.warn(`Slider-KM: Available keys for voltage "${voltage}":`, 
          Object.keys(distanceLookupTable).filter(k => k.startsWith(voltage))
        );
      }
    } finally {
      this.isUpdating = false;
    }
  },

  /**
    * Handle slider change
    * Finds the closest matching amperage for the selected voltage and auto-selects it
    */
  onSliderChange() {
    // Prevent infinite loops during synchronization
    if (this.isUpdating) {
      return;
    }

    this.isUpdating = true;

    try {
      const form = document.querySelector(".sabway-form");
      const distanceSlider = form.querySelector("#sab-distance-range");
      let sliderValue = parseInt(distanceSlider.value);

      // Get the selected voltage
      const voltageSelected = form.querySelector(
        'input[name="voltage"]:checked'
      );
      if (!voltageSelected) {
        console.log("Slider-KM: No voltage selected in onSliderChange");
        return; // No voltage selected, do nothing
      }

      const voltage = voltageSelected.value;
      console.log(`Slider-KM: onSliderChange - voltage="${voltage}", sliderValue=${sliderValue}`);

      // Find the closest matching amperage for the selected voltage
      const closestMatch = this.findClosestMatch(sliderValue, voltage);

      if (closestMatch) {
        const { amperage, distance, range } = closestMatch;

        console.log(`Slider-KM: Found closest match - amperage="${amperage}", distance=${distance}`);

        // Select the amperage radio button
        const amperageInput = form.querySelector(
          `input[name="amperage"][value="${amperage}"]`
        );
        if (amperageInput) {
          amperageInput.checked = true;
          amperageInput.dispatchEvent(new Event("change", { bubbles: true }));
        } else {
          console.warn(`Slider-KM: Could not find amperage input for value="${amperage}"`);
        }

        // Update the distance range display
        this.updateDistanceRangeDisplay({
          min: parseInt(range.split("–")[0]),
          max: parseInt(range.split("–")[1]),
          midpoint: distance,
        });

        // Clamp the slider value to the allowed range
        const minKm = parseInt(distanceSlider.min);
        const maxKm = parseInt(range.split("–")[1]);
        if (sliderValue < minKm) {
          distanceSlider.value = minKm;
          sliderValue = minKm;
        } else if (amperage === "38,4AH" && sliderValue > maxKm) {
          distanceSlider.value = maxKm;
          sliderValue = maxKm;
        }
        console.log(
          `Slider value ${sliderValue}km matched to ${voltage}-${amperage} (midpoint: ${distance}km)`
        );
      } else {
        console.warn(`Slider-KM: No closest match found for voltage="${voltage}"`);
      }
    } finally {
      this.isUpdating = false;
    }
  },

  /**
    * Find the closest matching amperage for a given voltage and distance
    * @param {number} sliderValue - The current slider value in km
    * @param {string} voltage - The selected voltage
    * @returns {Object|null} Object with amperage, and distance, or null if no match found
    */
  findClosestMatch(sliderValue, voltage) {
    let closestMatch = null;
    let closestDistance = Infinity;

    // Get all keys for this voltage
    const voltageKeys = Object.keys(distanceLookupTable).filter(k => k.startsWith(voltage));
    console.log(`Slider-KM: findClosestMatch - voltage="${voltage}"`);
    console.log(`Slider-KM: Found ${voltageKeys.length} combinations for this voltage:`, voltageKeys);

    // Iterate through combinations for the selected voltage
    for (const [key, data] of Object.entries(distanceLookupTable)) {
      if (!key.startsWith(voltage)) {
        continue;
      }

      // Calculate distance from slider value to this combination's midpoint
      const distance = Math.abs(sliderValue - data.midpoint);

      // Update closest match if this is closer
      if (distance < closestDistance) {
        closestDistance = distance;
        const [, amperage] = key.split("-");
        closestMatch = {
          amperage,
          distance: data.midpoint,
          range: `${data.min}–${data.max}`,
        };
      }
    }

    if (closestMatch) {
      console.log(`Slider-KM: Closest match distance=${closestDistance}, amperage="${closestMatch.amperage}"`);
    } else {
      console.warn(`Slider-KM: NO matches found for voltage="${voltage}". Looking for keys starting with "${voltage}"`);
    }

    return closestMatch;
  },

  /**
   * Update the distance range display in the span element
   * @param {Object} distanceData - Object with min, max, and midpoint values
   */
  updateDistanceRangeDisplay(distanceData) {
    const rangeSpan = document.querySelector(".eco-distance-for-slider");
    if (rangeSpan) {
      rangeSpan.textContent = `${distanceData.min}–${distanceData.max}km`;
    }
  },
};

/**
 * Export the synchronization module
 */
export { sliderKmSync };
