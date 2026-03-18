$(function () {

  function getSelectedActivities() {
    let activities = [];

    $(".activity-checkbox:checked").each(function () {
      activities.push($(this).val());
    });

    return activities;
  }

  $("#reservationForm").on("submit", function (event) {
    event.preventDefault();

    let nom = $("#nom").val();
    let prenom = $("#prenom").val();
    let email = $("#email").val();
    let telephone = $("#telephone").val();
    let date_arrivee = $("#date_arrivee").val();
    let date_depart = $("#date_depart").val();
    let nb_personnes = $("#nb_personnes").val();
    let activities = getSelectedActivities();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/reserve.php",
      data: {
        nom: nom,
        prenom: prenom,
        email: email,
        telephone: telephone,
        date_arrivee: date_arrivee,
        date_depart: date_depart,
        nb_personnes: nb_personnes,
        activities: JSON.stringify(activities)
      }
    }).done(function (obj) {
      $("#reservationMessage").html(
        "<div class='alert alert-info'>" + obj.message + "</div>"
      );
      $("#reservationForm")[0].reset();
    }).fail(function (e) {
      console.log(e);
      $("#reservationMessage").html(
        "<div class='alert alert-danger'>Erreur réseau pendant la réservation.</div>"
      );
    });
  });

  $("#loginForm").on("submit", function (event) {
    event.preventDefault();

    let email = $("#login_email").val();
    let password = $("#login_password").val();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/login.php",
      data: {
        email: email,
        password: password
      }
    }).done(function (obj) {
      $("#loginMessage").html(
        "<div class='alert alert-info'>" + obj.message + "</div>"
      );
    }).fail(function (e) {
      console.log(e);
      $("#loginMessage").html(
        "<div class='alert alert-danger'>Erreur réseau pendant la connexion.</div>"
      );
    });
  });

  $("#serviceForm").on("submit", function (event) {
    event.preventDefault();

    let email = $("#service_email").val();
    let service_name = $("#service_name").val();
    let quantity = $("#quantity").val();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/add_service.php",
      data: {
        email: email,
        service_name: service_name,
        quantity: quantity
      }
    }).done(function (obj) {
      $("#serviceMessage").html(
        "<div class='alert alert-info'>" + obj.message + "</div>"
      );
    }).fail(function (e) {
      console.log(e);
      $("#serviceMessage").html(
        "<div class='alert alert-danger'>Erreur réseau pendant l'ajout de prestation.</div>"
      );
    });
  });

  $("#btnInvoice").on("click", function () {
    let email = $("#email").val();

    if (email === "") {
      $("#invoiceBox").html(
        "<div class='alert alert-warning'>Entre d'abord l'email du client.</div>"
      );
      return;
    }

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/invoice.php",
      data: {
        email: email
      }
    }).done(function (obj) {
      if (!obj.success) {
        $("#invoiceBox").html(
          "<div class='alert alert-warning'>" + obj.message + "</div>"
        );
        return;
      }

      $("#invoiceBox").html(
        "<div class='alert alert-secondary'>" +
          "<strong>Facture prévisionnelle</strong><br>" +
          "Hébergement : " + obj.room_total + " €<br>" +
          "Activités : " + obj.activities_total + " €<br>" +
          "Prestations : " + obj.services_total + " €<br>" +
          "Réduction : -" + obj.discount_amount + " €<br>" +
          "Arrhes : -" + obj.deposit + " €<br>" +
          "<strong>Total : " + obj.total + " €</strong>" +
        "</div>"
      );
    }).fail(function (e) {
      console.log(e);
      $("#invoiceBox").html(
        "<div class='alert alert-danger'>Erreur réseau pendant le calcul de la facture.</div>"
      );
    });
  });

});