window.addEventListener("load", function() {
  document.querySelector("#whitelist_enabled").addEventListener("change", function() {
    document.querySelector("#whitelist_hidden").style.display = (this.checked == true ? "block" : "none");
    document.querySelector("#blacklist_enabled").disabled = (this.checked == true ? true : false);
    if (this.checked === true) {
      document.querySelector("#blacklist_enabled").checked = false;
      addClass(document.querySelector("label[for='blacklist_enabled']"), "is-disabled");
    } else {
      removeClass(document.querySelector("label[for='blacklist_enabled']"), "is-disabled");
    }
  });

  document.querySelector("#blacklist_enabled").addEventListener("change", function() {
    document.querySelector("#blacklist_hidden").style.display = (this.checked == true ? "block" : "none");
    document.querySelector("#whitelist_enabled").disabled = (this.checked == true ? true : false);
    if (this.checked === true) {
      document.querySelector("#whitelist_enabled").checked = false;
      addClass(document.querySelector("label[for='whitelist_enabled']"), "is-disabled");
    } else {
      removeClass(document.querySelector("label[for='whitelist_enabled']"), "is-disabled");
    }
  });

  document.querySelector("#age_enabled").addEventListener("change", function() {
    document.querySelector("#age_hidden").style.display = (this.checked == true ? "block" : "none");
  });
});
