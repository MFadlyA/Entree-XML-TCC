const hamBurger = document.querySelector(".toggle-btn");
const hamBurger2 = document.querySelector(".toggle-btn2");

hamBurger.addEventListener("click", function () {
  document.querySelector("#sidebar").classList.toggle("expand");
});

hamBurger2.addEventListener("click", function () {
  document.querySelector("#sidebar").classList.toggle("expand");
});

document.addEventListener("DOMContentLoaded", function () {
  const activeItem = document.querySelector(".sidebar-item.active");

  const sidebarItems = document.querySelectorAll(".sidebar-item");

  sidebarItems.forEach((item) => {
    if (!item.classList.contains("active")) {
      item.addEventListener("mouseenter", function () {
        if (activeItem) {
          activeItem.classList.remove("active");
        }
      });

      item.addEventListener("mouseleave", function () {
        if (activeItem) {
          activeItem.classList.add("active");
        }
      });
    }
  });
});
