$(document).ready(function() {
    let currentRecordId = null;
    let searchQuery = '';

    // Fetch and display records
    function loadRecords(query = '') {
        $.ajax({
            url: 'api.php',
            type: 'GET',
            success: function(data) {
                let records = JSON.parse(data);
                let html = '';

                // Filter records based on the search query
                records = records.filter(record => 
                    record.name.toLowerCase().includes(query.toLowerCase()) ||
                    record.email.toLowerCase().includes(query.toLowerCase()) ||
                    record.age.toString().includes(query)
                );

                records.forEach(record => {
                    html += `<tr>
                                <td>${record.id}</td>
                                <td>${record.name}</td>
                                <td>${record.email}</td>
                                <td>${record.age}</td>
                                <td>
                                    <button class="btn btn-info btn-sm updateRecord" data-id="${record.id}">Update</button>
                                    <button class="btn btn-danger btn-sm deleteRecord" data-id="${record.id}">Delete</button>
                                </td>
                             </tr>`;
                });
                $('#recordTable').html(html);
            }
        });
    }
    loadRecords();
     // Search records
     $('#searchQuery').on('input', function() {
       searchQuery = $(this).val();
        loadRecords(searchQuery);
    });
    
    // Open modal to create new record
    $('#createRecordBtn').on('click', function() {
        $('#recordModalLabel').text('Create Record');
        $('#name').val('');
        $('#email').val('');
        $('#age').val('');
        currentRecordId = null;
        $('#recordModal').modal('show');
    });

    // Save record (Create or Update)
    $('#saveRecord').on('click', function() {
        let name = $('#name').val();
        let email = $('#email').val();
        let age = $('#age').val();

        if (name && email && age) {
            let data = { name: name, email: email, age: age };
            let method = currentRecordId ? 'PUT' : 'POST';
            let url = 'api.php';

            if (currentRecordId) {
                url += `?id=${currentRecordId}`;
            }

            $.ajax({
                url: url,
                type: method,
                data: JSON.stringify(data),
                success: function() {
                    $('#recordModal').modal('hide');
                    loadRecords(searchQuery);
                }
            });
        } else {
            alert("All fields are required!");
        }
    });

    // Open modal to update record
    $(document).on('click', '.updateRecord', function() {
        let id = $(this).data('id');
        $.ajax({
            url: `api.php?id=${id}`,
            type: 'GET',
            success: function(data) {
                let record = JSON.parse(data);
                $('#recordModalLabel').text('Update Record');
                $('#name').val(record.name);
                $('#email').val(record.email);
                $('#age').val(record.age);
                currentRecordId = record.id;
                $('#recordModal').modal('show');
            }
        });
    });

       // Open delete confirmation modal
    $(document).on('click', '.deleteRecord', function() {
        currentRecordId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    // Confirm delete record
    $('#confirmDelete').on('click', function() {
        if (currentRecordId) {
            $.ajax({
                url: `api.php?id=${currentRecordId}`,
                type: 'DELETE',
                success: function() {
                    $('#deleteModal').modal('hide');
                    loadRecords(searchQuery);

                    // Show the toast notification
                    let deleteToast = new bootstrap.Toast(document.getElementById('deleteToast'));
                    deleteToast.show();
                }
            });
        }
    });
});