$(document).ready(function() {
  // Navigation entre sections
  $(".nav-link").click(function(e) {
    e.preventDefault();
    $(".nav-link").removeClass("active");
    $(this).addClass("active");
    $(".section").removeClass("active");
    $("#" + $(this).data("section")).addClass("active");
  });

  // Réserver un livre
  $(".reserve-btn").click(function() {
    let titre = $(this).siblings("h3").text();
    alert("✅ Vous avez réservé : " + titre);
  });

  // Rejoindre la liste d’attente si le livre est indisponible
  $(".waitlist-btn").click(function() {
    let titre = $(this).siblings("h3").text();
    let confirmJoin = confirm("Le livre '" + titre + "' est indisponible.\nSouhaitez-vous rejoindre la liste d’attente ?");
    if (confirmJoin) {
      alert("📋 Vous avez été ajouté(e) à la liste d’attente pour '" + titre + "'.");
    }
  });

  // Évaluation d’un livre
  $(".evaluer-btn").click(function() {
    let titre = $(this).siblings("h3").text();
    let note = prompt("Évaluer " + titre + " sur 5 ⭐");
    if (note) alert("Merci ! Vous avez attribué " + note + " étoiles.");
  });

  // Prolonger un emprunt
  $(".extend-btn").click(function() {
    alert("📅 Demande de prolongation envoyée à l’administration.");
  });

  // Envoi de message
  $("#sendMessage").click(function() {
    let msg = $("#messageText").val();
    if (msg.trim() === "") return alert("Veuillez écrire un message !");
    alert("✉️ Message envoyé à l’administration !");
    $("#messageText").val("");
  });

  // Génération de reçu PDF (simulation)
  $("#generatePDF").click(function() {
    alert("📄 Reçu PDF généré (fonction à implémenter via jsPDF ou backend).");
  });
});
