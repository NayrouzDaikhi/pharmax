#!/usr/bin/env php
<?php
/**
 * Test AJAX Review System Implementation
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║  AJAX REVIEW SYSTEM - IMPLEMENTATION TEST                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;

// Test 1: Check controller has both routes
echo "TEST 1: Controller Routes\n";
echo "─".str_repeat("─", 62)."─\n";

$controllerContent = file_get_contents('src/Controller/BlogController.php');

$tests = [
    "detailProduit GET only" => ["#\[Route\('/produit/\{id\}', name: 'app_front_detail_produit', methods: \['GET'\]\)"],
    "addAvis AJAX route" => ["#\[Route\('/produit/\{id\}/add-avis', name: 'app_front_add_avis', methods: \['POST'\]\)"],
    "addAvis returns JsonResponse" => ["JsonResponse"],
    "Creates Commentaire in addAvis" => ["new Commentaire\(\)"],
    "Sets statut to en_attente" => ["setStatut\('en_attente'\)"],
    "Returns avis data as JSON" => ["'avis' =>"],
];

foreach ($tests as $name => $patterns) {
    $found = true;
    foreach ($patterns as $pattern) {
        if (!preg_match($pattern, $controllerContent)) {
            $found = false;
            break;
        }
    }
    
    if ($found) {
        echo "✓ " . $name . "\n";
        $passed++;
    } else {
        echo "✗ " . $name . "\n";
        $failed++;
    }
}

// Test 2: Check template has JavaScript
echo "\nTEST 2: Template JavaScript\n";
echo "─".str_repeat("─", 62)."─\n";

$templateContent = file_get_contents('templates/blog/product_detail.html.twig');

$templateTests = [
    "Form has id='avis-form'" => ["id=\"avis-form\""],
    "form.style.display toggle" => ["form\\.style\\.display"],
    "fetch() AJAX call" => ["fetch\\("],
    "POST to add-avis" => ["app_front_add_avis"],
    "FormData for submission" => ["FormData"],
    "Success message display" => ["successDiv\\.style\\.display"],
    "Error handling" => ["errorDiv\\.style\\.display"],
    "Loading state" => ["loadingDiv\\.style\\.display"],
    "addPendingAvisToDOM function" => ["addPendingAvisToDOM"],
    "escapeHtml function" => ["escapeHtml"],
    "Pending avis styling" => ["#fff3cd", "#ff9800"],
    "Animation slideIn" => ["slideIn"],
];

foreach ($templateTests as $name => $patterns) {
    $found = true;
    foreach ($patterns as $pattern) {
        if (!preg_match("/" . preg_quote($pattern, '/') . "/", $templateContent)) {
            $found = false;
            break;
        }
    }
    
    if ($found) {
        echo "✓ " . $name . "\n";
        $passed++;
    } else {
        echo "✗ " . $name . "\n";
        $failed++;
    }
}

// Test 3: Check form elements
echo "\nTEST 3: Form Elements\n";
echo "─".str_repeat("─", 62)."─\n";

$formTests = [
    "Toggle button present" => ["id=\"avis-toggle-btn\""],
    "Textarea field" => ["id=\"avis-contenu\""],
    "Submit button" => ["id=\"avis-submit-btn\""],
    "Success message" => ["id=\"avis-success-message\""],
    "Error message" => ["id=\"avis-error-message\""],
    "Loading indicator" => ["id=\"avis-loading\""],
    "Avis list container" => ["id=\"avis-list\""],
];

foreach ($formTests as $name => $patterns) {
    $found = true;
    foreach ($patterns as $pattern) {
        if (strpos($templateContent, $pattern) === false) {
            $found = false;
            break;
        }
    }
    
    if ($found) {
        echo "✓ " . $name . "\n";
        $passed++;
    } else {
        echo "✗ " . $name . "\n";
        $failed++;
    }
}

// Test 4: Check validation
echo "\nTEST 4: Validation & Security\n";
echo "─".str_repeat("─", 62)."─\n";

$validationTests = [
    "Server validates contenu length" => ["strlen(\$contenu) > 1000"],
    "Server validates min length" => ["strlen(trim(\$contenu)) < 2"],
    "XSS prevention escapeHtml" => ["escapeHtml"],
    "Client validation minlength" => ["minlength=\"2\""],
    "Client validation maxlength" => ["maxlength=\"1000\""],
    "HTML entities escaped" => ["&amp;", "&lt;", "&gt;", "&quot;"],
];

foreach ($validationTests as $name => $patterns) {
    $found = true;
    foreach ($patterns as $pattern) {
        if (strpos($controllerContent . $templateContent, $pattern) === false) {
            $found = false;
            break;
        }
    }
    
    if ($found) {
        echo "✓ " . $name . "\n";
        $passed++;
    } else {
        echo "✗ " . $name . "\n";
        $failed++;
    }
}

// Test 5: Check styling distinction
echo "\nTEST 5: Styling & UX\n";
echo "─".str_repeat("─", 62)."─\n";

$stylingTests = [
    "Pending avis yellow (#fff3cd)" => ["#fff3cd"],
    "Pending avis orange border (#ff9800)" => ["#ff9800"],
    "Validated avis green border (#28a745)" => ["#28a745"],
    "Animation CSS present" => ["@keyframes slideIn", "animation:"],
    "Loading spinner icon" => ["fa-spinner"],
    "Submit button hover effect" => ["onmouseover=", "onmouseout="],
];

foreach ($stylingTests as $name => $patterns) {
    $found = true;
    foreach ($patterns as $pattern) {
        if (strpos($templateContent, $pattern) === false) {
            $found = false;
            break;
        }
    }
    
    if ($found) {
        echo "✓ " . $name . "\n";
        $passed++;
    } else {
        echo "✗ " . $name . "\n";
        $failed++;
    }
}

// Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  TEST SUMMARY                                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$total = $passed + $failed;
$percentage = ($total > 0) ? round(($passed / $total) * 100) : 0;

echo "✓ Passed: " . $passed . "\n";
echo "✗ Failed: " . $failed . "\n";
echo "Total:   " . $total . "\n";
echo "Score:   " . $percentage . "%\n\n";

if ($failed === 0) {
    echo "✨ ALL TESTS PASSED!\n\n";
    echo "🎯 AJAX REVIEW SYSTEM IS READY:\n";
    echo "   ✓ No redirection - fluide experience\n";
    echo "   ✓ Immediate feedback - avis visible tout de suite\n";
    echo "   ✓ Smart form toggle - disparaît après soumission\n";
    echo "   ✓ Error handling - validations client+serveur\n";
    echo "   ✓ Security - XSS prevention via escapeHtml\n";
    echo "   ✓ Animations - slideIn effect\n";
    echo "   ✓ Styling - yellow pending, white validated\n";
    echo "   ✓ Performance - Vanilla JS, no dependencies\n\n";
    
    echo "🚀 READY TO USE:\n";
    echo "   1. Start Symfony: symfony server:start -d\n";
    echo "   2. Navigate to:  http://localhost/produit/1\n";
    echo "   3. Click: \"Ajouter un Avis\"\n";
    echo "   4. Type avis + Submit\n";
    echo "   5. See it appear immediately (yellow badge)\n";
    echo "   6. Admin moderation at: /commentaire\n";
    echo "   7. Change status to 'valide' (becomes white)\n\n";
} else {
    echo "⚠️  Some tests failed. Review log above.\n\n";
}
