//--------forma za unos korisnika
function insertForm(page) {
    var output = '<table class="table table-hover"><tbody>';
    output += '<tr><th scope="col">name</th><td><input type="text" id="IME"></td></tr>';
    output += '<tr><th scope="col">last name</th><td><input type="text" id="PREZIME"></td></tr>';
    output += '<tr><th scope="col">phone</th><td><input type="" id="BRMOB"></td></tr>';
    output += '<tr><th scope="col">OIB</th><td><input type="" id="OIB"></td></tr>';
    output += '<tr><th scope="col">email</th><td><input type="text" id="EMAIL"></td></tr>';
    output += '<tr><th scope="col">sex</th><td><input type="text" id="SPOL"></td></tr>';
    output += '<tr><th scope="col">password</th><td><input type="text" id="PASSWORD"></td></tr>';
    output += '</table>';
    output += '<button type="button" class="btn btn-warning" id="spremiKor">save <i class="fas fa-save"></i></button> ';
    output += '<button type="button" class="btn btn-success" onclick="showKorisnici(' + page + ')">cancel <i class="fas fa-window-close"></i></button>';
    $("#container").html(output);
}


//-------------------------------------------------------------
function showKorisnici(page) {
    var tablica = '<div><br><button type="button" class="btn btn-success bt1" onclick="insertForm(' + page + ')">Insert <i class="fa fa-pen" aria-hidden="true"></i></button></div><br>';
    tablica += '<table class="table table-fluid" id="myTable"><thead><tr>';
    tablica += '<th scope="col">name</th><th scope="col">last name</th><th scope="col">phone</th><th scope="col">email</th>';
    tablica += '<th scope="col">OIB</th><th scope="col">sex</th>'
    tablica += '<th scope="col">action</th></tr></thead>';

    if (page == null || page == "" || page == undefined) {
        page = 1;
    } else {
        page++
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: {"projekt": projekt, 
               "procedura": "p_get_korisnik", 
               "perPage": perPage, 
               "page": page 
            },
        success: function (data) {
            var jsonBody = JSON.parse(data);
            var errcode = jsonBody.h_errcode;
            var message = jsonBody.h_message;
            var count = jsonBody.count;
            tablica += '<tbody>';

            if (message == null || message == "", errcode == null || errcode == 0) {
                $.each(jsonBody.data, function (k, v) {
                    tablica += '<tr><td>' + v.ime + '</td>';
                    tablica += '<td>' + v.prezime + '</td>';
                    tablica += '<td>' + v.BRMOB + '</td>';
                    tablica += '<td>' + v.email + '</td>';
                    tablica += '<td>' + v.OIB + '</td>';
                    tablica += '<td>' + v.SPOL + '</td>';
                    tablica += '<td><button type="button" class="btn btn-primary" onclick="showKorisnik(' + v.ID + ',' + page + ')">Edit <i class="fas fa-edit"></i></button> ';
                    tablica += '<button type="button" class="btn btn-danger" onclick="delKorisnik(' + v.ID + ',' + page + ')">Delete <i class="far fa-trash-alt"></i></button></td></tr>';
                });
                tablica += '</tbody></table>';
                tablica += `<script>$(document).ready( function () {$('#myTable').DataTable();} );</script>`;
               // tablica += pagination(page, perPage, count);
                $("#container").html(tablica);
            } else {
                if (errcode == 999) {
                    $("#container").html(loginForm);
                } else {
                    Swal.fire(message + '.' + errcode);
                }
            }
            refresh();
        },
        error: function (xhr, textStatus, error) {
            console.log(xhr.statusText);
            console.log(textStatus);
            console.log(error);
        },
        async: true
    });
}
//-----------------------------------------------------------------------------
function showKorisnik(ID, page) {
    var tablica = '<table class="table table-hover"><tbody>';
    $.ajax({
        type: 'POST',
        url: url,
        data: { "projekt": projekt, 
                "procedura": "p_get_korisnik",
                "ID": ID },
        success: function (data) {
            var jsonBody = JSON.parse(data);
            var errcode = jsonBody.h_errcode;
            var message = jsonBody.h_message;

            if (message == null || message == "", errcode == null || errcode == 0) {
                $.each(jsonBody.data, function (k, v) {
                    tablica += '<tr><th scope="col">ID</th><td><input type="text" id="ID" value="' + v.ID + '" readonly></td></tr>';
                    tablica += '<tr><th scope="col">name</th><td><input type="text" id="IME" value="' + v.ime + '"></td></tr>';
                    tablica += '<tr><th scope="col">last name</th><td><input type="text" id="PREZIME" value="' + v.prezime + '"></td></tr>';
                    tablica += '<tr><th scope="col">phone</th><td><input type="text" id="BRMOB" value="' + v.BRMOB + '"></td></tr>';
                    tablica += '<tr><th scope="col">OIB</th><td><input type="text" id="OIB" value="' + v.OIB + '"></td></tr>';
                    tablica += '<tr><th scope="col">email</th><td><input type="text" id="EMAIL" value="' + v.email + '"></td></tr>';
                    tablica += '<tr><th scope="col">sex</th><td><input type="text" id="SPOL" value="' + v.SPOL + '"></td></tr>';
                    tablica += '</table>';
                    tablica += '<button type="button" class="btn btn-warning" id="spremiKor">Save <i class="fas fa-save"></i></button> ';
                    tablica += '<button type="button" class="btn btn-success" onclick="showKorisnici(' /*+ page */+ ')">Cancel <i class="fas fa-window-close"></i></button>';
                });
                $("#container").html(tablica);
            } else {
                if (errcode == 999) {
                    $("#container").html(loginForm);
                } else {
                    Swal.fire(message + '.' + errcode);
                }
            }
            refresh();
        },
        error: function (xhr, textStatus, error) {
            console.log(xhr.statusText);
            console.log(textStatus);
            console.log(error);
        },
        async: true

    });
}

//-----------------------SAVE KORISNIK---------------------------
$(document).on('click', '#spremiKor', function () {
    var ime = $('#IME').val();
    var prezime = $('#PREZIME').val();
    var BRMOB = $('#BRMOB').val();
    var OIB = $('#OIB').val();
    var email = $('#EMAIL').val();
    var SPOL = $('#SPOL').val();
    var PASSWORD = $('#PASSWORD').val();
    var ID = $('#ID').val();

    if (ime == null || ime == "") {
        Swal.fire('Molimo unesite ime korisnika');
    } else if (prezime == null || prezime == "") {
        Swal.fire('Molimo unesite prezime korisnika');
    } else if (BRMOB == null || BRMOB == "") {
        Swal.fire('Molimo unesite broj mobitela korisnika');
    } else if (OIB == null || OIB == "") {
        Swal.fire('Molimo unesite OIB korisnika');
    } else if (email == null || email == "") {
        Swal.fire('Molimo unesite email korisnika');
    } else if (SPOL == null || SPOL == "") {
        Swal.fire('Molimo unesite spol korisnika');
    }  else if ((PASSWORD == null || PASSWORD == "") && (ID == null || ID == "")) {
        Swal.fire('Molimo unesite lozinku korisnika');
    } else {
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                "projekt": projekt,
                "procedura": "p_save_korisnik",
                "ID": ID,
                "ime": ime,
                "prezime": prezime,
                "BRMOB": BRMOB,
                "email": email,
                "OIB": OIB,
                "SPOL": SPOL,
                "PASSWORD": PASSWORD
            },
            success: function (data) {
                var jsonBody = JSON.parse(data);
                var errcode = jsonBody.h_errcode;
                var message = jsonBody.h_message;
                console.log(data);

                if ((message == null || message == "") && (errcode == null || errcode == 0)) {
                    Swal.fire('Uspješno se unijeli korisnika');
                } else {
                    Swal.fire(message + '.' + errcode);
                }
                refresh();
                showKorisnici();
            },
            error: function (xhr, textStatus, error) {
                console.log(xhr.statusText);
                console.log(textStatus);
                console.log(error);
            },
            async: true
        });
    }
})

//-------------------Brisanje korisnika---------------
function delKorisnik(ID, page){
    Swal.fire({
        title: 'Želite li zaista obrisati korisnika?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Da, obriši korisnika!',
        cancelButtonText: 'Ipak nemoj!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    "projekt": projekt,
                    "procedura": "p_delete_korisnik",
                    "ID": ID
                },
                success: function (data) {
                    var jsonBody = JSON.parse(data);
                    var errcode = jsonBody.h_errcode;
                    var message = jsonBody.h_message;
                    console.log(data);

                    if ((message == null || message == "") && (errcode == null || errcode == 0)) {
                        Swal.fire(
                            'Uspješno ',
                            'ste obrisali korisnika',
                            'success'
                        );
                    } else {
                        Swal.fire(message + '.' + errcode);
                    }
                    refresh();
                    showKorisnici();
                },
                error: function (xhr, textStatus, error) {
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                },
                async: true
            });
        }
    })
}