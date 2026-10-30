<?php include '../includes/header.php'; 
$city_id = (int)$city_id;

// Fetch all categories, types, and treatments
$query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type, dct.treatment 
          FROM dr_categories dc 
          LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
          ORDER BY dc.cat_name ASC, dct.type ASC";
$result = mysqli_query($con, $query);

// Group data by Category
$all_specialities = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cat_id = $row['dr_cat_id'];
        if (!isset($all_specialities[$cat_id])) {
            $all_specialities[$cat_id] = [
                'cat_name' => $row['cat_name'],
                'types' => []
            ];
        }
        if ($row['dr_cat_type_id']) {
            $all_specialities[$cat_id]['types'][] = [
                'id' => $row['dr_cat_type_id'],
                'type' => $row['type'],
                'treatment' => $row['treatment']
            ];
        }
    }
}
?>

<!-- Navbar -->
<?php include BASE_PATH.'/includes/menu.php'; ?>

<style>
    .speciality-directory-section {
        background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);
        padding: 40px 0 60px;
    }
    .search-wrapper {
        background: #fff;
        padding: 20px 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        margin-top: -40px;
        position: relative;
        z-index: 10;
        border-bottom: 4px solid #2f80ed;
    }
    .search-input-group {
        position: relative;
    }
    .search-input-group i {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #2f80ed;
        font-size: 1.2rem;
    }
    .search-input-group input {
        padding: 15px 20px 15px 50px;
        border-radius: 10px;
        border: 2px solid #eef2f7;
        font-size: 1.1rem;
        transition: all 0.3s;
        background: #f8f9fc;
    }
    .search-input-group input:focus {
        border-color: #2f80ed;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.1);
        outline: none;
    }
    .category-title-box {
        background: #fff;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        margin-top: 40px;
        border-left: 5px solid #2f80ed;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .category-title-box i {
        font-size: 1.8rem;
        color: #2f80ed;
    }
    .category-title-box h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }
    .disease-card {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        height: 100%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        border: 1px solid #eef2f7;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .disease-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.08);
        border-color: #2f80ed;
    }
    .disease-urdu {
        font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Arial', sans-serif;
        font-size: 1.3rem;
        color: #111;
        text-align: right;
        direction: rtl;
        margin-bottom: 10px;
        line-height: 1.8;
    }
    .disease-eng {
        font-size: 0.85rem;
        color: #666;
        font-weight: 600;
        margin-bottom: 15px;
    }
    .btn-find-doc {
        background: #eef6ff;
        color: #2f80ed;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
        display: inline-block;
        text-align: center;
    }
    .btn-find-doc:hover {
        background: #2f80ed;
        color: #fff;
    }
    .no-results {
        display: none;
        text-align: center;
        padding: 50px 20px;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .no-results i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 20px;
    }
</style>

<!-- Page Header -->
<section class="page-header py-5 text-white" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?= BASE_URL ?>admin/inc/uploads/default/hosp.jpg') center/cover;">
    <div class="container text-center pb-4">
        <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">All Specialities & Diseases</h1>
        <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">Find the right specialist for your disease or treatment</p>
    </div>
</section>

<!-- Main Directory Section -->
<section class="speciality-directory-section">
    <div class="container">
        
        <!-- Top Live Search Bar -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="search-wrapper" data-aos="fade-up" data-aos-delay="300">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" id="liveSearchInput" class="form-control" placeholder="Search disease (Urdu) or speciality (English)... e.g., دل، Cardiologist" autocomplete="off">
                    </div>
                    <div class="text-center mt-3 text-muted small">
                        <i class="fas fa-info-circle me-1"></i> Type to instantly filter results.
                    </div>
                </div>
            </div>
        </div>

        <div id="directoryContainer">
            <?php if (!empty($all_specialities)): ?>
                <?php foreach ($all_specialities as $cat_id => $data): ?>
                    <?php if (count($data['types']) > 0): ?>
                        
                        <!-- Category Box (Hide/Show based on search) -->
                        <div class="category-group" data-category-name="<?= strtolower(htmlspecialchars($data['cat_name'])) ?>">
                            
                            <!-- Category Heading -->
                            <div class="category-title-box" data-aos="fade-right">
                                <i class="fas fa-stethoscope"></i>
                                <h3><?= htmlspecialchars($data['cat_name']) ?></h3>
                            </div>

                            <!-- Diseases / Treatments Grid -->
                            <div class="row g-3">
                                <?php foreach ($data['types'] as $index => $type): 
                                    // Search matching ke liye data string create karein
                                    $search_string = strtolower($type['type'] . ' ' . $type['treatment'] . ' ' . $data['cat_name']);
                                ?>
                                    <div class="col-lg-3 col-md-4 col-6 disease-item" data-search-text="<?= htmlspecialchars($search_string) ?>" data-aos="fade-up" data-aos-delay="<?= ($index % 4) * 50 ?>">
                                        <div class="disease-card">
                                            <div>
                                                <!-- Urdu Treatment -->
                                                <div class="disease-urdu">
                                                    <?= !empty($type['treatment']) ? htmlspecialchars($type['treatment']) : 'نام دستیاب نہیں' ?>
                                                </div>
                                                <!-- English Type -->
                                                <div class="disease-eng">
                                                    <?= htmlspecialchars($type['type']) ?>
                                                </div>
                                            </div>
                                            <!-- Link to Doctors Filtered by this Type -->
                                            <a href="<?= BASE_URL ?>doctors?search=&city=<?= $city_id ?>&hospital=&specialization=<?= $type['id'] ?>" class="btn-find-doc">
                                                <i class="fas fa-user-md me-1"></i> Find Doctors
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center">No specialities found in the database.</div>
            <?php endif; ?>
        </div>

        <!-- No Results Found Message -->
        <div class="no-results" id="noResults">
            <i class="fas fa-search-minus"></i>
            <h3>No Results Found</h3>
            <p class="text-muted">We couldn't find any disease or speciality matching your search.</p>
        </div>

    </div>
</section>

<!-- Footer -->
<?php include BASE_PATH.'/includes/footer.php'; ?>

<!-- Live Search JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearchInput');
    const categoryGroups = document.querySelectorAll('.category-group');
    const noResults = document.getElementById('noResults');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let totalVisibleItems = 0;

        categoryGroups.forEach(function(group) {
            const items = group.querySelectorAll('.disease-item');
            let groupHasVisibleItems = false;

            items.forEach(function(item) {
                const searchText = item.getAttribute('data-search-text');
                
                // Agar search text match karta hai ya search box khali hai
                if (searchText.includes(searchTerm) || searchTerm === '') {
                    item.style.display = 'block';
                    groupHasVisibleItems = true;
                    totalVisibleItems++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Agar group ke andar koi item visible hai to group dikhao, warna chupao
            if (groupHasVisibleItems) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });

        // Agar kuch bhi nahi mila to "No Results" show karein
        if (totalVisibleItems === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    });
});
</script>