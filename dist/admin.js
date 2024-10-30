/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};

/*
 * Site Chat - Remove auto-added Freemius links
 */

document.addEventListener("DOMContentLoaded", function () {
  const wascRemoveFreemiusMenuItems = document.querySelectorAll(".fs-submenu-item.kaira-site-chat");

  if (wascRemoveFreemiusMenuItems) {
    wascRemoveFreemiusMenuItems.forEach(item => {
      item.closest("li").remove();
    });
  }

  const rateClick = document.querySelector(".wasc-rating-click");
  const rateShow = document.querySelector(".wasc-notice-rate");

  if (rateClick) {
    rateClick.addEventListener("click", () => {
      rateClick.style.display = "none";
      rateShow.style.display = "block";
    });
  }
});
/******/ })()
;