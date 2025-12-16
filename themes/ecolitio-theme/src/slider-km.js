/**
 * Battery Distance Range Synchronization Module
 * Handles bidirectional synchronization between distance slider and voltage/amperage inputs
 * Maps battery specifications (voltage + amperage) to distance ranges
 */

/**
 * Distance lookup table mapping voltage + amperage combinations to distance ranges
 * Format: "voltage-amperage" => { min: km, max: km, midpoint: km }
 * Note: Amperage values use commas (4,8AH) and uppercase (AH) as per WooCommerce attributes
 */
const distanceLookupTable = {
  // 24V Battery
  "24V-4,8AH": { min: 8, max: 10, midpoint: 9 },
  "24V-9,6AH": { min: 16, max: 20, midpoint: 18 },
  "24V-14,4AH": { min: 24, max: 30, midpoint: 27 },
  "24V-19,2AH": { min: 32, max: 40, midpoint: 36 },
  "24V-24AH": { min: 40, max: 50, midpoint: 45 },
  "24V-28,8AH": { min: 48, max: 58, midpoint: 53 },
  "24V-33,6AH": { min: 56, max: 68, midpoint: 62 },
  "24V-38,4AH": { min: 64, max: 78, midpoint: 71 },

  // 36V Battery
  "36V-4,8AH": { min: 12, max: 15, midpoint: 13 },
  "36V-9,6AH": { min: 24, max: 30, midpoint: 27 },
  "36V-14,4AH": { min: 36, max: 45, midpoint: 40 },
  "36V-19,2AH": { min: 48, max: 60, midpoint: 54 },
  "36V-24AH": { min: 60, max: 75, midpoint: 67 },
  "36V-28,8AH": { min: 72, max: 90, midpoint: 81 },
  "36V-33,6AH": { min: 84, max: 105, midpoint: 94 },
  "36V-38,4AH": { min: 96, max: 120, midpoint: 108 },

  // 48V Battery
  "48V-4,8AH": { min: 15, max: 18, midpoint: 16 },
  "48V-9,6AH": { min: 30, max: 36, midpoint: 33 },
  "48V-14,4AH": { min: 45, max: 54, midpoint: 49 },
  "48V-19,2AH": { min: 60, max: 72, midpoint: 66 },
  "48V-24AH": { min: 75, max: 90, midpoint: 82 },
  "48V-28,8AH": { min: 90, max: 108, midpoint: 99 },
  "48V-33,6AH": { min: 105, max: 126, midpoint: 115 },
  "48V-38,4AH": { min: 120, max: 144, midpoint: 132 },

  // 52V Battery
  "52V-4,8AH": { min: 16, max: 20, midpoint: 18 },
  "52V-9,6AH": { min: 32, max: 40, midpoint: 36 },
  "52V-14,4AH": { min: 48, max: 60, midpoint: 54 },
  "52V-19,2AH": { min: 64, max: 80, midpoint: 72 },
  "52V-24AH": { min: 80, max: 100, midpoint: 90 },
  "52V-28,8AH": { min: 96, max: 120, midpoint: 108 },
  "52V-33,6AH": { min: 112, max: 140, midpoint: 126 },
  "52V-38,4AH": { min: 128, max: 160, midpoint: 144 },

  // 60V Battery
  "60V-4,8AH": { min: 19, max: 23, midpoint: 21 },
  "60V-9,6AH": { min: 38, max: 46, midpoint: 42 },
  "60V-14,4AH": { min: 57, max: 69, midpoint: 63 },
  "60V-19,2AH": { min: 76, max: 92, midpoint: 84 },
  "60V-24AH": { min: 95, max: 115, midpoint: 105 },
  "60V-28,8AH": { min: 115, max: 138, midpoint: 126 },
  "60V-33,6AH": { min: 134, max: 161, midpoint: 147 },
  "60V-38,4AH": { min: 153, max: 184, midpoint: 168 },
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

    // Get all relevant form elements
    const voltageInputs = form.querySelectorAll('input[name="voltage"]');
    const amperageInputs = form.querySelectorAll('input[name="amperage"]');
    const distanceSlider = form.querySelector("#sab-distance-range");

    if (!distanceSlider) {
      console.warn("Distance slider not found");
      return;
    }

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
      const voltageSelected = form.querySelector('input[name="voltage"]:checked');
      const amperageSelected = form.querySelector('input[name="amperage"]:checked');
      const distanceSlider = form.querySelector("#sab-distance-range");

      // Only update if both voltage and amperage are selected
      if (!voltageSelected || !amperageSelected) {
        return;
      }

      const voltage = voltageSelected.value;
      const amperage = amperageSelected.value;
      const key = `${voltage}-${amperage}`;

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
      }
    } finally {
      this.isUpdating = false;
    }
  },

  /**
   * Handle slider change
   * Finds the closest matching voltage/amperage combination and auto-selects it
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
      const sliderValue = parseInt(distanceSlider.value);

      // Find the closest matching voltage/amperage combination
      const closestMatch = this.findClosestMatch(sliderValue);

      if (closestMatch) {
        const { voltage, amperage, distance, range } = closestMatch;

        // Select the voltage radio button
        const voltageInput = form.querySelector(
          `input[name="voltage"][value="${voltage}"]`
        );
        if (voltageInput) {
          voltageInput.checked = true;
          voltageInput.dispatchEvent(new Event("change", { bubbles: true }));
        }

        // Select the amperage radio button
        const amperageInput = form.querySelector(
          `input[name="amperage"][value="${amperage}"]`
        );
        if (amperageInput) {
          amperageInput.checked = true;
          amperageInput.dispatchEvent(new Event("change", { bubbles: true }));
        }

        // Update the distance range display
        this.updateDistanceRangeDisplay({ min: parseInt(range.split('–')[0]), max: parseInt(range.split('–')[1]), midpoint: distance });

        console.log(
          `Slider value ${sliderValue}km matched to ${voltage}-${amperage} (midpoint: ${distance}km)`
        );
      }
    } finally {
      this.isUpdating = false;
    }
  },

  /**
   * Find the closest matching voltage/amperage combination for a given distance
   * @param {number} sliderValue - The current slider value in km
   * @returns {Object|null} Object with voltage, amperage, and distance, or null if no match found
   */
  findClosestMatch(sliderValue) {
    let closestMatch = null;
    let closestDistance = Infinity;

    // Iterate through all combinations in the lookup table
    for (const [key, data] of Object.entries(distanceLookupTable)) {
      // Calculate distance from slider value to this combination's midpoint
      const distance = Math.abs(sliderValue - data.midpoint);

      // Update closest match if this is closer
      if (distance < closestDistance) {
        closestDistance = distance;
        const [voltage, amperage] = key.split("-");
        closestMatch = {
          voltage,
          amperage,
          distance: data.midpoint,
          range: `${data.min}–${data.max}`,
        };
      }
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
