window.addEventListener("load", function() {
  if (document.querySelector(".addresult") != null) {
    document.querySelector(".addresult").addEventListener("click", function() {
      document.querySelector("#addresult").showModal();
    });
  }
});
