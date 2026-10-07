const tabs = document.querySelectorAll(".tab-btn");
const panels = document.querySelectorAll(".tab-panel");

tabs.forEach((tab) => {
   tab.addEventListener("click", () => {
      const target = tab.dataset.tab;

      tabs.forEach((t) => {
         t.classList.remove("active");
         t.setAttribute("aria-selected", "false");
      });
      panels.forEach((p) => p.classList.remove("active"));

      tab.classList.add("active");
      tab.setAttribute("aria-selected", "true");

      const panel = document.getElementById("tab-" + target);
      panel.classList.add("active");
   });
});

// Popup logic
const overlay = document.getElementById("popupOverlay");
const openBtn = document.getElementById("openPopup");
const closeBtn = document.getElementById("closePopup");

openBtn.addEventListener("click", () => overlay.classList.add("open"));
closeBtn.addEventListener("click", () => overlay.classList.remove("open"));
overlay.addEventListener("click", (e) => {
   if (e.target === overlay) overlay.classList.remove("open");
});
document.addEventListener("keydown", (e) => {
   if (e.key === "Escape") overlay.classList.remove("open");
});
