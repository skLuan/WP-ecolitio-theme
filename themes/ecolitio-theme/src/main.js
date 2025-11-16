import "../styles/tailwind.css";
import "iconify-icon";
import { startNavigation } from "./navigation";
import { formController } from "./formController";
import Swiper from "swiper";
import { Navigation, Pagination } from 'swiper/modules';
// import Swiper styles
import "swiper/css";
import "swiper/css/navigation";
// import "swiper/css/pagination";

let _swiper;

if (document.querySelector(".swiper-sab-batery")) {
  
  const swiper = new Swiper(".swiper-sab-batery", {
    slidesPerView: 1,
    spaceBetween: 8,
    autoHeight: true,
    allowTouchMove: false,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
      addIcons: false,
    },
    modules: [Navigation, Pagination],
  });

  _swiper = swiper;
  console.log(swiper);
}
export const nextSlide = () => {
  _swiper.slideNext();
};
export const swiperSab = () => {
  return _swiper;
};

const baseUrl = "https://ecolitio.com/wp-content/themes/ecolitio-theme/";
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

  const changeImagePatinete = () => {
    const imageInterior = document.querySelector('#image-patinete-interior');
    const imageExterior = document.querySelector('#image-patinete-exterior');
    const ubicaciones = document.querySelectorAll('input[name="ubicacion-de-bateria"]');
    ubicaciones.forEach(ubicacion => {
      if(ubicacion.checked && ubicacion.value.includes('Interna')){
        imageInterior.classList.remove('opacity-0');
        imageExterior.classList.add('opacity-0');
      }else if(ubicacion.checked && ubicacion.value.includes('Externa')){
        imageInterior.classList.add('opacity-0');
        imageExterior.classList.remove('opacity-0');
      }
    });
  }

  if (this.documentURI.includes("bateria-sabway")) {
    formController();
    changeImagePatinete();
    
    // Add event listeners to update image when radio button selection changes
    const ubicaciones = document.querySelectorAll('input[name="ubicacion-de-bateria"]');
    ubicaciones.forEach(ubicacion => {
      ubicacion.addEventListener('change', changeImagePatinete);
    });
  }

  const updatePriceVariableProduct = () => {
    const bigPrice = document.querySelector('h5.eco-price');
    const siblingDescription = document.querySelector('.woocommerce-product-details__short-description');
    // Store initial price backup
    const initPriceNode = bigPrice ? bigPrice.innerHTML : null;
    
    // Get the variation container
    const singleNodeAtributes = document.querySelector('form.variations_form .woocommerce-variation');
    
    if (!singleNodeAtributes || !bigPrice || !siblingDescription) {
      return; // Exit if required elements don't exist
    }
    
    // Setup MutationObserver to detect changes in variation
    const observer = new MutationObserver(() => {
      const varDescription = singleNodeAtributes.querySelector('.woocommerce-variation-description');
      const varPrice = singleNodeAtributes.querySelector('.woocommerce-variation-price');
      
      // Update bigPrice with varPrice HTML content
      if (varPrice && varPrice.innerHTML.trim()) {
        bigPrice.innerHTML = varPrice.innerHTML;
      }
      
      // Insert or replace varDescription before siblingDescription
      if (varDescription && varDescription.innerHTML.trim()) {
        // Check if description already exists
        const existingVarDesc = siblingDescription.previousElementSibling;
        
        if (existingVarDesc && existingVarDesc.classList.contains('woocommerce-variation-description')) {
          // Replace existing description
          existingVarDesc.innerHTML = varDescription.innerHTML;
        } else {
          // Create new description element and insert before siblingDescription
          const newDescElement = document.createElement('div');
          newDescElement.className = 'woocommerce-variation-description';
          newDescElement.innerHTML = varDescription.innerHTML;
          siblingDescription.parentNode.insertBefore(newDescElement, siblingDescription);
        }
      }
    });
    
    // Observe changes in the variation container
    observer.observe(singleNodeAtributes, {
      childList: true,
      subtree: true,
      characterData: true
    });
  };

  // Initialize price update for variable products
  if (document.querySelector('form.variations_form')) {
    updatePriceVariableProduct();
  }
});
