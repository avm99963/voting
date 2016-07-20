window.addEventListener("load", function() {
  if (document.querySelector("#aboutbtn")) {
    document.querySelector("#aboutbtn").addEventListener("click", function() {
      document.querySelector("#about").showModal();
    });
  }
});
