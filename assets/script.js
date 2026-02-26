        $(document).ready(function () {
            // Global JS Error Handler for users
            window.onerror = function (message, source, lineno, colno, error) {
                alert("JavaScript Error! Please report this: " + message + " at line " + lineno);
                console.error("JS Error:", message, source, lineno, colno, error);
            };

            // Global AJAX Error Handler
            $(document).ajaxError(function (event, jqxhr, settings, thrownError) {
                console.error("Global AJAX Error!", settings.url, thrownError);
            });

            // Check protocol
            if (window.location.protocol === 'file:') {
                alert("CRITICAL: You are opening index.php via file:// protocol. AJAX will NOT work. Please use http://localhost:8000 instead.");
                $('#businessList').html('<tr><td colspan="7" class="text-center text-danger">Error: file:// protocol detected. Use http://localhost:8000</td></tr>');
                return;
            }

            // Fetch and Load Businesses (Call early)
            fetchBusinesses();

            // Set Raty Defaults Safely
            if (typeof $.fn.raty !== 'undefined' && typeof $.fn.raty.defaults !== 'undefined') {
                $.fn.raty.defaults.path = 'https://cdn.jsdelivr.net/npm/raty-js@3.1.1/lib/images';
            }

            // Fetch and Load Businesses
            function fetchBusinesses() {
                $.ajax({
                    url: 'includes/actions.php',
                    method: 'POST',
                    data: { action: 'fetch_businesses' },
                    success: function (response) {
                        try {
                            // jQuery auto-parses if Content-Type is application/json
                            let data = typeof response === 'string' ? JSON.parse(response) : response;
                            let rows = '';
                            if (!data || data.length === 0) {
                                rows = '<tr><td colspan="7" class="text-center">No businesses found.</td></tr>';
                            } else {
                                data.forEach(function (row) {
                                    rows += `
                        <tr id="row_${row.id}">
                            <td><span class="b-id">#${row.id}</span></td>
                            <td class="fw-semibold">${row.name}</td>
                            <td><i class="fas fa-map-marker-alt text-secondary me-2"></i>${row.address}</td>
                            <td>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-phone-alt text-muted small me-2"></i>
                                    <span class="text-truncate" style="max-width: 180px;" title="${row.phone}">${row.phone}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope text-muted small me-2"></i>
                                    <span class="text-truncate" style="max-width: 180px;" title="${row.email}">${row.email}</span>
                                </div>
                            </td>
                            <td>
                                <div class="avg-rating" data-score="${row.avg_rating}"></div>
                                <span class="ms-1 rating-score-badge">${row.avg_rating > 0 ? row.avg_rating + ' / 5.0' : 'New'}</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-primary btn-sm rate-btn me-1" data-id="${row.id}" data-name="${row.name}">
                                    <i class="fas fa-star text-warning"></i> Rate
                                </button>
                                <div class="btn-group">
                                    <button class="btn btn-outline-secondary border-0 btn-sm edit-btn" data-id="${row.id}" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-outline-danger border-0 btn-sm delete-btn" data-id="${row.id}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                                });
                            }
                            $('#businessList').html(rows);

                            // Initialize Read-only Raty for average rating
                            $('.avg-rating').each(function () {
                                let score = $(this).data('score');
                                $(this).raty({
                                    readOnly: true,
                                    score: score,
                                    half: true,
                                    starType: 'i',
                                    starOn: 'fas fa-star text-warning',
                                    starOff: 'far fa-star text-secondary',
                                    starHalf: 'fas fa-star-half-alt text-warning'
                                });
                            });
                        } catch (e) {
                            console.error("JSON Parse Error:", e, response);
                            $('#businessList').html('<tr><td colspan="7" class="text-center text-danger">Error parsing data. Check console.</td></tr>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        console.log("XHR Response:", xhr.responseText);
                        $('#businessList').html('<tr><td colspan="7" class="text-center text-danger">Failed to load data from server.</td></tr>');
                    }
                });
            }

            // fetchBusinesses(); // Handled at top now

            // Reset Modal for Adding
            $('#addBtn').click(function () {
                $('#businessModalLabel').text('Add New Business');
                $('#businessForm')[0].reset();
                $('#business_id').val('');
                $('#action').val('add_business');
            });

            // Handle Add/Edit Form Submit
            $('#businessForm').submit(function (e) {
                e.preventDefault();
                let $btn = $('#saveBtn');
                $btn.prop('disabled', true).text('Saving...');

                console.log("Submitting Business Data:", $(this).serialize());

                $.ajax({
                    url: 'includes/actions.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        console.log("Business Action Response:", response);
                        try {
                            let res = typeof response === 'string' ? JSON.parse(response) : response;
                            if (res.status === 'success') {
                                $('#businessModal').modal('hide');
                                fetchBusinesses();
                            } else {
                                alert("Error: " + res.message);
                            }
                        } catch (err) {
                            console.error("JSON Parse Error:", err, response);
                            alert("Invalid response from server. Check console.");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        alert("Network error: " + error);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Save Business');
                    }
                });
            });

            // Handle Edit Button Click
            $(document).on('click', '.edit-btn', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: 'includes/actions.php',
                    method: 'POST',
                    data: { action: 'get_business', id: id },
                    success: function (response) {
                        let data = typeof response === 'string' ? JSON.parse(response) : response;
                        $('#businessModalLabel').text('Edit Business');
                        $('#business_id').val(data.id);
                        $('#name').val(data.name);
                        $('#address').val(data.address);
                        $('#phone').val(data.phone);
                        $('#email').val(data.email);
                        $('#action').val('edit_business');
                        $('#businessModal').modal('show');
                    }
                });
            });

            // Handle Delete Button Click
            $(document).on('click', '.delete-btn', function () {
                if (confirm('Are you sure you want to delete this business?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: 'includes/actions.php',
                        method: 'POST',
                        data: { action: 'delete_business', id: id },
                        success: function (response) {
                            let res = typeof response === 'string' ? JSON.parse(response) : response;
                            if (res.status === 'success') {
                                $(`#row_${id}`).fadeOut();
                            }
                        }
                    });
                }
            });

            // Handle Rate Button Click
            $(document).on('click', '.rate-btn', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#ratingForm')[0].reset();
                $('#rating_business_id').val(id);
                $('#ratingBusinessName').text(name);

                // Initialize Interactive Raty for rating
                try {
                    $('#star_rating').empty().raty({
                        scoreName: 'rating_value',
                        half: true,
                        starType: 'i',
                        starOn: 'fa-solid fa-star text-warning',
                        starOff: 'fa-regular fa-star text-secondary',
                        starHalf: 'fa-solid fa-star-half-stroke text-warning',
                        target: '#rating_hint',
                        targetKeep: true,
                        targetType: 'number',
                        targetFormat: 'Rating: {score}'
                    });
                } catch (e) {
                    alert("Error loading stars! " + e.message);
                }

                $('#ratingModal').modal('show');
            });

            // Handle Rating Form Submit
            $('#ratingForm').submit(function (e) {
                e.preventDefault();
                let rating = $('input[name="rating_value"]').val();
                if (!rating || rating == 0 || rating == "") {
                    alert("Please select at least 0.5 stars!");
                    return false;
                }

                let $btn = $('#submitRatingBtn');
                $btn.prop('disabled', true).text('Submitting...');
                $('#ratingAlert').addClass('d-none').removeClass('alert-success alert-danger');

                console.log("Submitting Rating Data:", $(this).serialize());
                $.ajax({
                    url: 'includes/actions.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        console.log("Rating Response:", response);
                        try {
                            let res = typeof response === 'string' ? JSON.parse(response) : response;
                            if (res.status === 'success') {
                                $('#ratingAlert').removeClass('d-none').addClass('alert-success').text('Rating submitted successfully!');
                                setTimeout(function () {
                                    $('#ratingModal').modal('hide');
                                    fetchBusinesses(); // Refresh table
                                }, 1500);
                            } else {
                                $('#ratingAlert').removeClass('d-none').addClass('alert-danger').text('Error: ' + res.message);
                            }
                        } catch (e) {
                            $('#ratingAlert').removeClass('d-none').addClass('alert-danger').text('Invalid response from server.');
                        }
                    },
                    error: function () {
                        $('#ratingAlert').removeClass('d-none').addClass('alert-danger').text('Network error. Please try again.');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Submit Rating');
                    }
                });
            });
        });
