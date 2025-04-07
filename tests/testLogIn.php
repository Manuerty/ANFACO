<?php
    include '../src/php/consultas.php';

    // Function to test 'comprueba_usuario'
    function test_comprueba_usuario() {
        // Test 1: Valid username and password
        $result = comprueba_usuario("Manuel", "contraseña");
        if (is_array($result) && count($result) == 4) {
            echo "Test 1 passed: Correct user and password.\n";
        } else {
            echo "Test 1 failed.\n";
        }

        // Test 2: Incorrect username
        $result = comprueba_usuario("manu", "contraseña");
        if ($result === 0) {
            echo "Test 2 passed: Incorrect username.\n";
        } else {
            echo "Test 2 failed.\n";
        }

        // Test 3: Incorrect password
        $result = comprueba_usuario("Manuel", "password");
        if ($result === 0) {
            echo "Test 3 passed: Incorrect password.\n";
        } else {
            echo "Test 3 failed.\n";
        }

        // Test 4: Empty username

        /*
        $result = comprueba_usuario("", "contraseña");
        if ($result === false) {
            echo "Test 4 passed: Empty username.\n";
        } else {
            echo "Test 4 failed.\n";
        }
        */
        

        // Test 5: Empty password

        /*
        $result = comprueba_usuario("Manuel", "");
        if ($result === false) {
            echo "Test 5 passed: Empty password.\n";
        } else {
            echo "Test 5 failed.\n";
        }
        */

    }

    // Execute the tests
    test_comprueba_usuario();
?>