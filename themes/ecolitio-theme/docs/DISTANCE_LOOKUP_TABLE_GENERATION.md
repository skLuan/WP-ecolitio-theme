# Distance Lookup Table Generation

## Overview

The `distanceLookupTable` in `slider-km.js` maps battery specifications (voltage + amperage) to estimated distance ranges in kilometers. This table supports both:

- **New Pattern**: Whole numbers (5AH, 10AH, 15AH, 20AH, 25AH, 30AH, 35AH, 40AH)
- **Old Pattern**: Decimal numbers with commas (4,8AH, 9,6AH, 14,4AH, 19,2AH, 24AH, 28,8AH, 33,6AH, 38,4AH)

The table includes entries for these voltage levels:
- 12V, 24V, 36V, 48V, 52V, 60V

**Total entries**: 96 combinations (6 voltages × 16 amperage values)

## Calculation Formula

Distance ranges are calculated using:

```
Watt-Hours (Wh) = Voltage × Amperage
Base Distance = Wh / 10 * Efficiency Factor
Midpoint = Base Distance
Min = Midpoint × 0.83 (±17% variation)
Max = Midpoint × 1.2 (±20% variation)
```

### Efficiency Factors by Voltage

| Voltage | Factor | Reason |
|---------|--------|--------|
| 12V     | 0.4    | Lower efficiency, more energy loss in system |
| 24V     | 0.7    | Moderate efficiency |
| 36V     | 0.9    | Good efficiency |
| 48V     | 1.0    | Baseline (reference point) |
| 52V     | 1.05   | Slightly better efficiency |
| 60V     | 1.1    | Best efficiency, less voltage drop loss |

## How to Regenerate the Table

If you need to update the amperage patterns or voltage levels, follow these steps:

### Step 1: Create the Generator Script

Create a new file `generate_distance_table.js` with this content:

```javascript
/**
 * Generate Distance Lookup Table
 * Creates distance ranges for all voltage and amperage combinations
 */

const voltages = [12, 24, 36, 48, 52, 60];
const amperagesNew = [5, 10, 15, 20, 25, 30, 35, 40];
const amperagesOld = [4.8, 9.6, 14.4, 19.2, 24, 28.8, 33.6, 38.4];

function calculateDistance(voltage, amperage) {
  const wattHours = voltage * amperage;
  
  const efficiencyFactors = {
    12: 0.4,
    24: 0.7,
    36: 0.9,
    48: 1.0,
    52: 1.05,
    60: 1.1
  };
  
  const efficiency = efficiencyFactors[voltage] || 1.0;
  const midpoint = Math.round((wattHours / 10) * efficiency);
  
  const min = Math.round(midpoint * 0.83);
  const max = Math.round(midpoint * 1.2);
  
  return { min, max, midpoint };
}

function formatAmperage(amp) {
  if (Number.isInteger(amp)) {
    return `${amp}AH`;
  }
  return `${amp.toFixed(1).replace('.', ',')}AH`;
}

console.log('// Auto-generated Distance Lookup Table\n');

// Generate entries...
voltages.forEach(voltage => {
  console.log(`// ${voltage}V Battery - New Pattern (5AH, 10AH, 15AH, ...)`);
  
  amperagesNew.forEach(amp => {
    const key = `"${voltage}V-${formatAmperage(amp)}"`;
    const distance = calculateDistance(voltage, amp);
    const entry = `${key}: { min: ${distance.min}, max: ${distance.max}, midpoint: ${distance.midpoint} },`;
    console.log(entry);
  });
  
  console.log('');
});

voltages.forEach(voltage => {
  console.log(`// ${voltage}V Battery - Old Pattern (4,8AH, 9,6AH, 14,4AH, ...)`);
  
  amperagesOld.forEach(amp => {
    const key = `"${voltage}V-${formatAmperage(amp)}"`;
    const distance = calculateDistance(voltage, amp);
    const entry = `${key}: { min: ${distance.min}, max: ${distance.max}, midpoint: ${distance.midpoint} },`;
    console.log(entry);
  });
  
  console.log('');
});
```

### Step 2: Run the Generator

```bash
node generate_distance_table.js
```

### Step 3: Copy Output

The script outputs the complete lookup table in the correct JavaScript format. Copy the output and replace the `distanceLookupTable` object in `slider-km.js`.

## Example Output

For 12V-5AH:
- Wh = 12 × 5 = 60
- Base = 60 / 10 × 0.4 = 2.4 ≈ 2 km (midpoint)
- Min = 2 × 0.83 = 1.66 ≈ 2 km
- Max = 2 × 1.2 = 2.4 ≈ 2 km

For 60V-40AH:
- Wh = 60 × 40 = 2400
- Base = 2400 / 10 × 1.1 = 264 km (midpoint)
- Min = 264 × 0.83 = 219 km
- Max = 264 × 1.2 = 317 km

## Current Table Summary

### New Pattern (Patinete, EcoLife)

| Voltage | 5AH  | 10AH | 15AH | 20AH | 25AH | 30AH | 35AH | 40AH |
|---------|------|------|------|------|------|------|------|------|
| 12V     | 2    | 5    | 7    | 10   | 12   | 14   | 17   | 19   |
| 24V     | 8    | 17   | 25   | 34   | 42   | 50   | 59   | 67   |
| 36V     | 16   | 32   | 49   | 65   | 81   | 97   | 113  | 130  |
| 48V     | 24   | 48   | 72   | 96   | 120  | 144  | 168  | 192  |
| 52V     | 27   | 55   | 82   | 109  | 137  | 164  | 191  | 218  |
| 60V     | 33   | 66   | 99   | 132  | 165  | 198  | 231  | 264  |

### Old Pattern (Sabway, Medida)

| Voltage | 4,8AH | 9,6AH | 14,4AH | 19,2AH | 24AH | 28,8AH | 33,6AH | 38,4AH |
|---------|-------|-------|--------|--------|------|--------|--------|--------|
| 24V     | 8     | 16    | 24     | 32     | 40   | 48     | 56     | 65     |
| 36V     | 16    | 31    | 47     | 62     | 78   | 93     | 109    | 124    |
| 48V     | 23    | 46    | 69     | 92     | 115  | 138    | 161    | 184    |
| 52V     | 26    | 52    | 79     | 105    | 131  | 157    | 183    | 210    |
| 60V     | 32    | 63    | 95     | 127    | 158  | 190    | 222    | 253    |

## Customization

To adjust distance calculations:

1. **Change Efficiency Factors**: Modify the `efficiencyFactors` object in the generator script
2. **Adjust Min/Max Ranges**: Change the 0.83 and 1.2 multipliers
3. **Add New Voltages**: Add entries to the `voltages` array
4. **Modify Amperage Patterns**: Update the `amperagesNew` or `amperagesOld` arrays

## Usage in Code

The slider automatically:
1. Looks up the distance range based on selected voltage + amperage
2. Sets the slider to the midpoint value
3. Displays the min-max range to users
4. Clamps slider values to valid ranges

Example:
```javascript
const voltage = "24V";
const amperage = "10AH";
const key = `${voltage}-${amperage}`; // "24V-10AH"
const distanceData = distanceLookupTable[key];
// Result: { min: 14, max: 20, midpoint: 17 }
```
