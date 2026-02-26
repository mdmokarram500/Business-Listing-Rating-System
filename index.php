<?php
// Fallback for non-AJAX form submissions
if (isset($_POST['action'])) {
    require_once __DIR__ . '/includes/db_connect.php';
    $_REQUEST = array_merge($_REQUEST, $_POST);
    include __DIR__ . '/includes/actions.php';
    // After action, redirect to clear POST data
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Listing & Rating System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css.css">
</head>

<body>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-0"><i class="fas fa-building me-2 text-primary"></i>Business
                        Directory</h2>
                    <p class="text-muted mt-1 mb-0">Discover and rate local businesses.</p>
                </div>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#businessModal"
                    id="addBtn">
                    <i class="fas fa-plus me-2"></i> New Business
                </button>
            </div>
        </div>

        <div class="simple-card">
            <div class="table-responsive">
                <table class="table align-middle" id="businessTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Company</th>
                            <th width="20%">Location</th>
                            <th width="15%">Contact</th>
                            <th width="20%">Community Rating</th>
                            <th width="20%" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="businessList">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <div class="text-muted">Loading directory...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Business Modal (Add/Edit) -->
    <div class="modal fade" id="businessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content simple-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="businessModalLabel">Add New Business</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="businessForm" action="index.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="action" value="add_business">
                        <input type="hidden" name="business_id" id="business_id">
                        <div class="mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save Business</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content simple-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Rate Business: <span id="ratingBusinessName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="ratingForm" action="index.php" method="POST">
                    <div class="modal-body">
                        <div id="ratingAlert" class="alert d-none"></div>
                        <input type="hidden" name="action" value="save_rating">
                        <input type="hidden" name="business_id" id="rating_business_id">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="r_name" id="r_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="r_email" id="r_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="r_phone" id="r_phone" class="form-control" required>
                        </div>
                        <div class="mb-3 text-center">
                            <label class="form-label d-block">Your Rating</label>
                            <div id="star_rating" class="rating-stars mb-2"></div>
                            <div class="fw-bold text-warning" id="rating_hint">Select Stars</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="submitRatingBtn">Submit Rating</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Raty Plugin (via CDN - using a reliable one) -->
    <script src="https://cdn.jsdelivr.net/npm/raty-js@3.1.1/lib/jquery.raty.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/raty-js@3.1.1/lib/jquery.raty.css">

    <script src="assets/script.js"></script>

</body>

</html>