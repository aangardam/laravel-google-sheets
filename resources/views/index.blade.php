<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Integrasi Laravel Google Sheets</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .btn-sinkronisasi {
            margin-bottom: 20px;
        }
        .table {
            background-color: #fff;
        }
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body class="antialiased">
    <div class="container">
        <h1>Integrasi Laravel Google Sheets</h1>
        <hr>
        <div class="text-right mb-3">
            <button class="btn btn-primary btn-sinkronisasi" data-toggle="modal" data-target="#syncModal">Sinkronisasi Data</button>
        </div>
        <table class="table table-striped table-bordered" id="dataTable">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">Jenis Kelamin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $key => $value)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $value->nik }}</td>
                        <td>{{ $value->name }}</td>
                        <td>{{ $value->address }}</td>
                        <td>{{ $value->gender }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
        <form method="POST" action="{{ url('sheets/post')}}">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="syncModalLabel">Sinkronisasi Data</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="inputNIK">Client ID <span style="color: red">*</span> </label>
                            <input type="text" name="clientId" class="form-control" id="clientId" placeholder="Masukkan Client ID" required>
                        </div>
                        <div class="form-group">
                            <label for="inputNamaSheet">Nama Sheet <span style="color: red">*</span> </label>
                            <input type="text" name="name" class="form-control" id="name" placeholder="Masukkan Nama Sheet" required>
                        </div>
                        <div class="text-right">
                            <a href="{{ asset('google_sheet.png') }}" target="_blank"><i> * cara mendapatkan client id dan nama sheet</i></a>
                        </div>
                        <hr>
                        Jika anda akan menggunakan file baru, maka share file tersebut ke email berikut:
                        <div class="input-group">
                            <input type="text" class="form-control" value="goole-sheet@seventh-voltage-431703-s5.iam.gserviceaccount.com" id="shareEmail" readonly>
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <button id="copyBtn" class="btn btn-link p-0">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" id="btn-sinkronisasi" class="btn btn-primary">Sinkronkan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('#dataTable').DataTable();

            $('#copyBtn').click(function() {
                var copyText = document.getElementById("shareEmail");
                copyText.select();
                document.execCommand("copy");
                alert("Email copied to clipboard");
            });

            $('#btn-sinkronisasi').click(function() {
                var clientId = $('#clientId').val();
                var sheetName = $('#name').val();

                $.ajax({
                    type: 'POST',
                    url: '{{ url("sheets/post") }}',
                    data: {
                        clientId: clientId,
                        name: sheetName
                    },
                    success: function(response) {
                        if(response.code == 500){
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            }).then(function() {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil disinkronkan!'
                            }).then(function() {
                                location.reload();
                            });
                        }

                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON.message || 'Terjadi kesalahan.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        }).then(function() {
                            location.reload();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
