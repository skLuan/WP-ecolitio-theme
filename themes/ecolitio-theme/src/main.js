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

  if (this.documentURI.includes("bateria-sabway")) {
    console.log("si es");
    formController();
  }
});
