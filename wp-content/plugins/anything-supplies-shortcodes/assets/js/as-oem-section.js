const carousel = document.getElementById("newProducts");
const cardWidth = carousel.querySelector(".product-card").offsetWidth + 25;

document.querySelector(".next").addEventListener("click", () => {
   carousel.scrollBy({ left: cardWidth, behavior: "smooth" });
});

document.querySelector(".prev").addEventListener("click", () => {
   carousel.scrollBy({ left: -cardWidth, behavior: "smooth" });
});
