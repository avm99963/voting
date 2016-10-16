function vote(id) {
  dynDialog.load("ajax/voteconfirm.php?id="+id);
}

function multiplevote() {
  var checked = document.querySelectorAll("input[type='checkbox']:checked");

  var votes = [];

  for (var i = 0; i < checked.length; i++) {
    votes[i] = checked[i].getAttribute("data-ballotid");
  }

  dynDialog.load("ajax/voteconfirm.php?id="+encodeURIComponent(votes.join(",")));
}

window.addEventListener("load", function() {
  document.querySelector("#multivote").addEventListener("click", multiplevote);
});
