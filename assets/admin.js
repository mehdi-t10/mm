$(function () {

  let adminConnected = false;

  function loadReservations() {
    $.ajax({
      method: "GET",
      dataType: "json",
      url: "api/admin_list_reservations.php"
    }).done(function (obj) {
      if (!obj.success) {
        $("#reservationsBox").html("<div class='alert alert-warning'>Impossible de charger les réservations.</div>");
        return;
      }

      let html = "";

      if (obj.reservations.length === 0) {
        html = "<div class='alert alert-secondary'>Aucune réservation pour le moment.</div>";
      } else {
        $.each(obj.reservations, function (index, reservation) {
          html += "<div class='border rounded p-3 mb-3 bg-light'>";
          html += "<strong>ID :</strong> " + reservation.id + "<br>";
          html += "<strong>Client :</strong> " + reservation.prenom + " " + reservation.nom + "<br>";
          html += "<strong>Email :</strong> " + reservation.email + "<br>";
          html += "<strong>Séjour :</strong> " + reservation.date_arrivee + " au " + reservation.date_depart + "<br>";
          html += "<strong>Personnes :</strong> " + reservation.nb_personnes + "<br>";
          html += "<strong>Statut :</strong> " + reservation.status + "<br>";
          html += "<strong>Chambre :</strong> " + reservation.room + "<br>";
          html += "<strong>Arrhes :</strong> " + reservation.deposit + " €<br>";
          html += "<strong>Réduction :</strong> " + reservation.discount_percent + " %<br>";

          html += "<div class='mt-3'>";

          html += "<button class='btn btn-success btn-sm me-2 btn-validate' data-id='" + reservation.id + "'>Valider</button>";

          html += "<input type='number' min='0' class='form-control form-control-sm d-inline-block me-2 deposit-input' style='width:120px;' id='deposit_" + reservation.id + "' placeholder='Arrhes'>";
          html += "<button class='btn btn-outline-primary btn-sm me-2 btn-set-deposit' data-id='" + reservation.id + "'>Mettre arrhes</button>";

          html += "<select class='form-select form-select-sm d-inline-block me-2 discount-select' style='width:120px;' id='discount_" + reservation.id + "'>";
          html += "<option value='0'>0%</option>";
          html += "<option value='10'>10%</option>";
          html += "<option value='20'>20%</option>";
          html += "<option value='50'>50%</option>";
          html += "</select>";
          html += "<button class='btn btn-outline-warning btn-sm btn-set-discount' data-id='" + reservation.id + "'>Mettre réduction</button>";

          html += "</div>";
          html += "</div>";
        });
      }

      $("#reservationsBox").html(html);
    }).fail(function (e) {
      console.log(e);
      $("#reservationsBox").html("<div class='alert alert-danger'>Erreur réseau.</div>");
    });
  }

  $("#adminLoginForm").on("submit", function (event) {
    event.preventDefault();

    let email = $("#admin_email").val();
    let password = $("#admin_password").val();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/admin_login.php",
      data: {
        email: email,
        password: password
      }
    }).done(function (obj) {
      $("#adminLoginMessage").html("<div class='alert alert-info'>" + obj.message + "</div>");

      if (obj.success) {
        adminConnected = true;
        loadReservations();
      }
    }).fail(function (e) {
      console.log(e);
      $("#adminLoginMessage").html("<div class='alert alert-danger'>Erreur réseau pendant la connexion admin.</div>");
    });
  });

  $(document).on("click", ".btn-validate", function () {
    if (!adminConnected) {
      return;
    }

    let id = $(this).data("id");

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/admin_validate_reservation.php",
      data: {
        id: id
      }
    }).done(function (obj) {
      alert(obj.message);
      loadReservations();
    }).fail(function (e) {
      console.log(e);
      alert("Erreur réseau pendant la validation.");
    });
  });

  $(document).on("click", ".btn-set-deposit", function () {
    if (!adminConnected) {
      return;
    }

    let id = $(this).data("id");
    let deposit = $("#deposit_" + id).val();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/admin_set_deposit.php",
      data: {
        id: id,
        deposit: deposit
      }
    }).done(function (obj) {
      alert(obj.message);
      loadReservations();
    }).fail(function (e) {
      console.log(e);
      alert("Erreur réseau pendant la mise à jour des arrhes.");
    });
  });

  $(document).on("click", ".btn-set-discount", function () {
    if (!adminConnected) {
      return;
    }

    let id = $(this).data("id");
    let discount = $("#discount_" + id).val();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/admin_set_discount.php",
      data: {
        id: id,
        discount: discount
      }
    }).done(function (obj) {
      alert(obj.message);
      loadReservations();
    }).fail(function (e) {
      console.log(e);
      alert("Erreur réseau pendant la mise à jour de la réduction.");
    });
  });

  $("#btnLoadDay").on("click", function () {
    if (!adminConnected) {
      return;
    }

    let day = $("#dayFilter").val();

    $.ajax({
      method: "GET",
      dataType: "json",
      url: "api/admin_day_requests.php",
      data: {
        day: day
      }
    }).done(function (obj) {
      if (!obj.success) {
        $("#dayRequestsBox").html("<div class='alert alert-warning'>Impossible de charger les demandes.</div>");
        return;
      }

      let html = "";

      if (obj.reservations.length === 0) {
        html = "<div class='alert alert-secondary'>Aucune demande validée pour ce jour.</div>";
      } else {
        $.each(obj.reservations, function (index, reservation) {
          html += "<div class='border rounded p-2 mb-2'>";
          html += "ID " + reservation.id + " - " + reservation.prenom + " " + reservation.nom + " (" + reservation.nb_personnes + " personnes)";
          html += "</div>";
        });
      }

      $("#dayRequestsBox").html(html);
    }).fail(function (e) {
      console.log(e);
      $("#dayRequestsBox").html("<div class='alert alert-danger'>Erreur réseau.</div>");
    });
  });

  $("#planActivityForm").on("submit", function (event) {
    event.preventDefault();

    if (!adminConnected) {
      return;
    }

    let reservation_id = $("#reservation_id").val();
    let day = $("#activity_day").val();
    let activity = $("#activity_name").val();
    let coach = $("#coach").val();
    let referee = $("#referee").val();
    let participants = $("#participants").val();

    $.ajax({
      method: "POST",
      dataType: "json",
      url: "api/admin_plan_activity.php",
      data: {
        reservation_id: reservation_id,
        day: day,
        activity: activity,
        coach: coach,
        referee: referee,
        participants: participants
      }
    }).done(function (obj) {
      $("#planActivityMessage").html("<div class='alert alert-info'>" + obj.message + "</div>");
      $("#planActivityForm")[0].reset();
    }).fail(function (e) {
      console.log(e);
      $("#planActivityMessage").html("<div class='alert alert-danger'>Erreur réseau pendant la planification.</div>");
    });
  });

});