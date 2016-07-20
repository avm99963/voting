window.addEventListener("load", function() {
  if (document.querySelector(".addvoting") != null) {
    document.querySelector(".addvoting").addEventListener("click", function() {
      document.querySelector("#addvoting").showModal();
      /* Or dialog.show(); to show the dialog without a backdrop. */
    });
  }

  if (document.querySelector(".filtervoting") != null) {
    document.querySelector(".filtervoting").addEventListener("click", function() {
      document.querySelector("#filtervoting").showModal();
      /* Or dialog.show(); to show the dialog without a backdrop. */
    });
  }
});
