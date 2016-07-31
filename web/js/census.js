window.addEventListener("load", function() {
  if (document.querySelector(".adduser") != null) {
    document.querySelector(".adduser").addEventListener("click", function() {
      document.querySelector("#adduser").showModal();
    });
  }
});
