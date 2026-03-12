<?php
require_once __DIR__ . '/connect.php';

$tattooRows = [];
$styleTagOptions = [];
$searchQuery = trim((string)($_GET['q'] ?? ''));
$selectedStyles = array_values(array_unique(array_filter(
    array_map(static fn($style) => normalize_style_key((string)$style), (array)($_GET['style'] ?? [])),
    static fn($style) => $style !== ''
)));

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function get_search_score(array $tattoo, string $searchQuery): int
{
    if ($searchQuery === '') {
        return 0;
    }

    $needle = strtolower($searchQuery);
    $title = strtolower((string)($tattoo['title'] ?? ''));
    $description = strtolower((string)($tattoo['description'] ?? ''));
    $styleNames = strtolower((string)($tattoo['style_names'] ?? ''));
    $score = 0;

    if ($title === $needle) {
        $score += 120;
    } elseif (str_starts_with($title, $needle)) {
        $score += 90;
    } elseif ($title !== '' && str_contains($title, $needle)) {
        $score += 70;
    }

    if ($styleNames === $needle) {
        $score += 80;
    } elseif ($styleNames !== '' && str_contains($styleNames, $needle)) {
        $score += 50;
    }

    if ($description !== '' && str_contains($description, $needle)) {
        $score += 25;
    }

    return $score;
}

function normalize_style_key(string $styleName): string
{
    $normalized = strtolower(trim($styleName));
    $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? $normalized;
    return trim($normalized, '-');
}

function style_key_from_image_path(string $imagePath): string
{
    if (preg_match('#/styles/([^/]+)/#i', str_replace('\\\\', '/', $imagePath), $matches) === 1) {
        return normalize_style_key((string)$matches[1]);
    }

    return '';
}

if ($db_ready && $mysqli instanceof mysqli) {
    $styleTagResult = $mysqli->query('SELECT tag_id, tag_name FROM style_tags ORDER BY tag_name ASC');

    if ($styleTagResult) {
        while ($styleTagRow = $styleTagResult->fetch_assoc()) {
            $styleTagOptions[] = $styleTagRow;
        }
    }

    $result = $mysqli->query(
        'SELECT t.tattoo_id, t.image_path, t.title, t.description,
                GROUP_CONCAT(DISTINCT st.tag_id ORDER BY st.tag_id) AS style_ids,
                GROUP_CONCAT(DISTINCT st.tag_name ORDER BY st.tag_name SEPARATOR ", ") AS style_names,
                GROUP_CONCAT(DISTINCT st.tag_name ORDER BY st.tag_name SEPARATOR "||") AS style_names_raw
         FROM tattoos t
         LEFT JOIN tattoo_style_tag tst ON tst.tattoo_id = t.tattoo_id
         LEFT JOIN style_tags st ON st.tag_id = tst.style_tag_id
         GROUP BY t.tattoo_id, t.image_path, t.title, t.description
         ORDER BY t.tattoo_id DESC'
    );

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rowStyleIds = array_values(array_filter(
                array_map('intval', explode(',', (string)($row['style_ids'] ?? ''))),
                static fn($styleId) => $styleId > 0
            ));

            $rowStyleKeys = array_values(array_filter(array_map(
                static fn($styleName) => normalize_style_key((string)$styleName),
                explode('||', (string)($row['style_names_raw'] ?? ''))
            )));

            if (empty($rowStyleKeys)) {
                $pathStyleKey = style_key_from_image_path((string)($row['image_path'] ?? ''));
                if ($pathStyleKey !== '') {
                    $rowStyleKeys[] = $pathStyleKey;
                }
            }

            if (!empty($selectedStyles) && empty(array_intersect($selectedStyles, $rowStyleKeys))) {
                continue;
            }

            $row['style_ids'] = $rowStyleIds;
            $row['_search_score'] = get_search_score($row, $searchQuery);
            $tattooRows[] = $row;
        }
    }

    usort($tattooRows, static function (array $left, array $right): int {
        if (($right['_search_score'] ?? 0) !== ($left['_search_score'] ?? 0)) {
            return ($right['_search_score'] ?? 0) <=> ($left['_search_score'] ?? 0);
        }

        return ((int)$right['tattoo_id']) <=> ((int)$left['tattoo_id']);
    });
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inkseek</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/grid.css">
    <link rel="stylesheet" href="styles/discover.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
    <script src="scripts/filters.js" defer></script>
</head>

<body>
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>
    <!-- Header Section -->
    <div id="discover-header">
        <header>
            <h1>Discover your next look</h1>
        </header>
        <form id="search-form" method="get" action="discover.php" role="search" aria-label="Tattoo search">
            <?php foreach ($selectedStyles as $selectedStyle): ?>
                <input type="hidden" name="style[]" value="<?php echo e($selectedStyle); ?>">
            <?php endforeach; ?>
            <div class="search-wrapper">
                <label for="search-bar" class="visually-hidden">Search</label>
                <input id="search-bar" type="search" name="q" value="<?php echo e($searchQuery); ?>"
                    placeholder="Search styles, locations, artists, etc..." />
                <button type="button" id="filter-button" aria-label="Open filters">
                    <img src="images/favicons/filter.png" alt="Filter icon">
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Overlay -->
    <div id="filter-overlay" class="filter-overlay">
        <div class="filter-content">
            <div class="filter-header">
                <h2>Filters</h2>
                <button id="close-filter" aria-label="Close filters">
                    <img src="images/favicons/cancel.png" alt="Close">
                </button>
            </div>

            <div class="filter-body">
                <div class="filter-section">
                    <h3>Style</h3>
                    <div class="filter-options">
                        <?php if (!empty($styleTagOptions)): ?>
                            <?php foreach ($styleTagOptions as $styleTag): ?>
                                <?php $styleKey = normalize_style_key((string)$styleTag['tag_name']); ?>
                                <label>
                                    <input type="checkbox" name="style" value="<?php echo e($styleKey); ?>"
                                        <?php echo in_array($styleKey, $selectedStyles, true) ? 'checked' : ''; ?>>
                                    <?php echo e($styleTag['tag_name']); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No styles available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Location Filter -->
                <div class="filter-section">
                    <h3>Location</h3>
                    <div class="filter-options">
                        <label for="location-input" class="visually-hidden">Location</label>
                        <input type="text" id="location-input" name="location" placeholder="Enter location...">
                    </div>
                </div>

                <!-- Price Filter -->
                <div class="filter-section">
                    <h3>Price</h3>
                    <div class="filter-options price-range">
                        <div class="price-input">
                            <label for="price-min">Min</label>
                            <input type="number" id="price-min" name="price-min" placeholder="$0" min="0">
                        </div>
                        <span class="price-separator">-</span>
                        <div class="price-input">
                            <label for="price-max">Max</label>
                            <input type="number" id="price-max" name="price-max" placeholder="$1000" min="0">
                        </div>
                    </div>
                </div>

                <!-- Star Ratings Filter -->
                <div class="filter-section">
                    <h3>Star Ratings</h3>
                    <div class="filter-options">
                        <label><input type="checkbox" name="rating" value="5"> ★★★★★ (5 stars)</label>
                        <label><input type="checkbox" name="rating" value="4"> ★★★★☆ (4+ stars)</label>
                        <label><input type="checkbox" name="rating" value="3"> ★★★☆☆ (3+ stars)</label>
                        <label><input type="checkbox" name="rating" value="2"> ★★☆☆☆ (2+ stars)</label>
                        <label><input type="checkbox" name="rating" value="1"> ★☆☆☆☆ (1+ stars)</label>
                    </div>
                </div>
            </div>

            <div class="filter-footer">
                <button id="clear-filters" class="secondary-btn">Clear All</button>
                <button id="apply-filters" class="primary-btn">Apply Filters</button>
            </div>
        </div>
    </div>

    <div id="grid-container">
        <?php if (!empty($tattooRows)): ?>
            <?php foreach ($tattooRows as $tattoo): ?>
                <div id="grid-item">
                    <a href="<?php echo 'tattoos.php?tattoo_id=' . (int)$tattoo['tattoo_id']; ?>">
                        <img src="<?php echo htmlspecialchars((string)$tattoo['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars((string)$tattoo['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tattoos matched your current search and style filter.</p>
        <?php endif; ?>
    </div>

    <!-- Footer Section -->
    <div id="footer-placeholder"></div>
</body>

</html>
<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>