describe('WhiskerHub - TC-WH-004: Pendaftaran Penjaga Kucing', () => {

  // Sila pastikan anda menukar emel dan kata laluan di bawah ke akaun pemilik biasa yang belum menjadi sitter
  const validEmail = 'test@whiskerhub.com';
  const validPassword = '123456';

  beforeEach(() => {
    // Log masuk terlebih dahulu
    cy.visit('/view/login.php');
    cy.get('#email').type(validEmail);
    cy.get('#password').type(validPassword);
    cy.get('form#loginForm').submit();
    cy.url().should('include', '/view/mainmenu.php');
  });

  it('Langkah 1 & 2: Isi borang permohonan sitter & semak navigasi menu', () => {
    // Mock lokasi geolocator sebelum melawat halaman
    cy.visit('/view/becomesitter.php', {
      onBeforeLoad(win) {
        cy.stub(win.navigator.geolocation, 'getCurrentPosition').callsArgWith(0, {
          coords: {
            latitude: 2.9935, // Lokasi Mock (contoh: Kajang/Bangi)
            longitude: 101.7874
          }
        });
      }
    });

    // Isi maklumat borang pendaftaran sitter
    cy.get('#bandar').clear().type('Bangi');
    cy.get('#negeri').clear().type('Selangor');

    // Pilih jenis servis
    cy.get('.service[value="boarding"]').check();
    cy.get('.service[value="grooming"]').check();

    // Kadar harga
    cy.get('#rate').clear().type('12.50');

    // Tangkap popup alert kejayaan
    const alertStub = cy.stub();
    cy.on('window:alert', alertStub);

    // Hantar permohonan
    cy.get('#sitterForm').submit();

    // Pastikan pendaftaran berjaya
    cy.wrap(alertStub).should('have.been.calledWith', 'Berjaya daftar sebagai sitter!');

    // Selepas berjaya, sistem sepatutnya melencongkan pengguna ke profile.php
    cy.url().should('include', '/view/profile.php');

    // Langkah 2: Melawat mainmenu.php untuk melihat jika link "Become a Sitter" disembunyikan
    cy.visit('/view/mainmenu.php');

    // Pautan Become a Sitter patut disembunyikan secara automatik
    cy.get('#becomeSitterLink').should('not.be.visible');
  });

});
