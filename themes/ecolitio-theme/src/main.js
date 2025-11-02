import "../styles/tailwind.css";
import "iconify-icon";
import { startNavigation } from "./navigation";
import { formController } from "./formController";


const baseUrl = "wp-content/themes/ecolitio-theme/";
document.addEventListener("DOMContentLoaded", function () {
  startNavigation();

  // Product Power Cycle Animation
  const powerCycleItems = document.querySelectorAll(".item-powercycle a");

  if (powerCycleItems.length > 0) {
    powerCycleItems.forEach((item) => {
      //const originalContent = item.innerHTML; // Store original content

      const img = document.createElement("img");
      img.src = baseUrl + "assets/powercycleBtn.svg"; // Set the image source
      if (!img.src) return; // If image src is undefined or null, return, leaving the original content
      img.alt = "PowerCycle"; // Set alt text for accessibility
      img.classList.add(
        "power-cycle-image",
        "md:min-w-[170px]",
        "md:!max-w-[170px]"
      ); // Add a class for styling

      // Clear existing content and append the image
      item.innerHTML = "";
      item.appendChild(img);
    });
  }



    

if(this.documentURI.includes('bateria-sabway')) {
  console.log('si es');
  formController();
}


});
