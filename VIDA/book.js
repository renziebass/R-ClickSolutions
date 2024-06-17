function btn_book() {
    const swalWithBootstrapButtons = Swal.mixin({
      customClass: {
        confirmButton: "btn btn-success",
        cancelButton: "btn btn-secondary me-1"
      },
      buttonsStyling: false
    });
    swalWithBootstrapButtons.fire({
      title: "First Name",
      input: "text",
      showCancelButton: true,
      confirmButtonText: "Next",
      cancelButtonText: "Cancel",
      reverseButtons: true,
      inputValidator: (result1) => {
        if (!result1) {
          return "Enter name!";
        } else {
        }
      },
      inputAttributes: {
      maxlength: "30"
    }
    }).then((result1) => {
      if (result1.isConfirmed) {
        swalWithBootstrapButtons.fire({
      title: "Last Name",
      input: "text",
      showCancelButton: true,
      confirmButtonText: "Next",
      reverseButtons: true,
      inputValidator: (result2) => {
        if (!result2) {
          return "Enter name!";
        } else {
        }
      },
      inputAttributes: {
      maxlength: "30"
    }
    }).then((result2) => {
      if (result2.isConfirmed) {
        swalWithBootstrapButtons.fire({
      title: "SELECT SERVICE",
      input: "select",
      inputOptions: {
      TREATMENTS : {
        pampering:"PAMPERING",
        facial:"FACIAL",
        massage:"MASSAGE",
        waxing:"WAXING",
        tinting:"TINTING",
        nailservice:"NAIL SERVICES",
        }
      },
      showCancelButton: true,
      confirmButtonText: "Next",
      cancelButtonText: "Cancel",
      reverseButtons: true
    }).then((result3) => {
      if (result3.isConfirmed) {
        swalWithBootstrapButtons.fire({
      title: "Select Date",
      input: "date",
      showCancelButton: true,
      confirmButtonText: "Next",
      cancelButtonText: "Cancel",
      reverseButtons: true,
      inputValidator: (result4) => {
        if (!result4) {
          return "Choose Date!";
        } else {
        }
      },
      inputAttributes: {
      maxlength: "30"
    }
    }).then((result4) => {
      if (result4.isConfirmed) {
        swalWithBootstrapButtons.fire({
      title: "Appointment Time",
      input: "time",
      confirmButtonText: "Confirm Appointment",
      reverseButtons: true,
      inputValidator: (result5) => {
        if (!result5) {
          return "Choose Time!";
        } else {
        }
      }

    }).then((result5) => {
      if (result5.isConfirmed) {
        window.location.href = 
        'mailto:Vidaspa@mail.com?subject=NEW APPOINTMENT&body='+result1.value+' '+result2.value+' - '+result3.value+' - '+result4.value+' - '+result5.value;
      } else {
        result.dismiss === Swal.DismissReason.cancel
      }
    });
    
      } else {
        result.dismiss === Swal.DismissReason.cancel
      }
    });
      } else {
        result.dismiss === Swal.DismissReason.cancel
      }
    });
        
        
      } else {
        result.dismiss === Swal.DismissReason.cancel
      }
    });
        
      } else {
        result.dismiss === Swal.DismissReason.cancel
      }
    });
  }