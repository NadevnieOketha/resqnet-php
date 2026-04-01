// Basic data & rendering logic to keep HTML reusable
const reportsData = [
  {
    title: "Flooding in Residential Area",
    desc: "Urgent need for rescue in the flooded area near Main Street.",
    reporter: "Sarah Miller",
  },
  {
    title: "Landslide Near Community Center",
    desc: "Landslide blocking access to the community center. Volunteers needed for debris removal.",
    reporter: "David Chen",
  },
  {
    title: "Landslide Near Community Center",
    desc: "Landslide blocking access to the community center. Volunteers needed for debris removal.",
    reporter: "David Chen",
  },
];

function renderReports(listEl, data) {
  const template = document.getElementById("report-item-template");
  listEl.innerHTML = "";
  data.forEach((item) => {
    const clone = template.content.cloneNode(true);
    clone.querySelector(".report-title").textContent = item.title;
    clone.querySelector(".report-desc").textContent = item.desc;
    clone.querySelector(
      ".report-meta"
    ).textContent = `Reported by: ${item.reporter}`;
    clone.querySelector(".assist-btn").addEventListener("click", () => {
      alert("Thank you! Your willingness to assist has been recorded.");
    });
    listEl.appendChild(clone);
  });
}

function setupNavigation() {
  const navButtons = document.querySelectorAll(".nav-item");
  navButtons.forEach((btn) =>
    btn.addEventListener("click", () => {
      navButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      // Placeholder - swap content section based on btn.dataset.section if needed
    })
  );
}

function setupActionsAsNav() {
  document.querySelectorAll(".action-card").forEach((card) => {
    card.addEventListener("click", () => {
      const target = card.dataset.section;
      const nav = document.querySelector(`.nav-item[data-section="${target}"]`);
      if (nav) nav.click();
    });
  });
}

function setupSidebarToggle() {
  const toggle = document.querySelector(".menu-toggle");
  const sidebar = document.querySelector(".sidebar");
  toggle.addEventListener("click", () => {
    const open = sidebar.classList.toggle("open");
    document.body.classList.toggle("menu-open", open);
    toggle.setAttribute("aria-expanded", String(open));
  });
  // Close on escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && sidebar.classList.contains("open")) {
      sidebar.classList.remove("open");
      document.body.classList.remove("menu-open");
      toggle.setAttribute("aria-expanded", "false");
    }
  });
}

function init() {
  const list = document.getElementById("reportList");
  renderReports(list, reportsData);
  setupNavigation();
  setupActionsAsNav();
  setupSidebarToggle();
  if (window.lucide) {
    window.lucide.createIcons();
  }
}

document.addEventListener("DOMContentLoaded", init);
