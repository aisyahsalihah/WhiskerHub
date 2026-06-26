describe('WhiskerHub - Log Masuk dan Kawalan Sesi', () => {

  it('Langkah 2: Ceroboh URL profile.php tanpa log masuk', () => {
    // Cuba akses halaman profile.php secara terus
    cy.visit('/view/profile.php');
    
    // Sepatutnya disekat dan dilencongkan kembali ke login.php
    // Nota: Sekarang kod anda melencongkan ke signin.php (yang tidak wujud), jadi ujian ini akan mengesan pepijat ini!
    cy.url().should('include', '/view/login.php');
  });

  it('Langkah 1: Log masuk dengan kredensial sah', () => {
    cy.visit('/view/login.php');

    // Masukkan emel dan kata laluan ujian (Sila ubah mengikut akaun Firestore anda)
    cy.get('#email').type('test@whiskerhub.com');
    cy.get('#password').type('password123');
    
    // Klik butang log masuk
    cy.get('form#loginForm').submit();

    // Sistem sepatutnya memaparkan alert kejayaan dan melencongkan pengguna
    // Cypress boleh menangkap alert seperti ini:
    cy.on('window:alert', (str) => {
      expect(str).to.equal('Login successful!');
    });

    // Semak sama ada pengguna dilencongkan ke mainmenu.php selepas berjaya
    cy.url().should('include', '/view/mainmenu.php');
  });

});
