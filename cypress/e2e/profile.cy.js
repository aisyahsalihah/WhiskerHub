describe('WhiskerHub - TC-WH-003: Pengurusan Profil Pengguna', () => {

  // Sila pastikan anda menukar emel dan kata laluan di bawah ke akaun yang sah dalam database anda
  const validEmail = 'test@whiskerhub.com';
  const validPassword = 'password123';

  beforeEach(() => {
    // Log masuk terlebih dahulu sebelum setiap ujian
    cy.visit('/view/login.php');
    cy.get('#email').type(validEmail);
    cy.get('#password').type(validPassword);
    cy.get('form#loginForm').submit();

    // Tunggu sehingga masuk ke menu utama, kemudian pergi ke profile.php
    cy.url().should('include', '/view/mainmenu.php');
    cy.visit('/view/profile.php');
  });

  it('Langkah 1: Kemaskini Maklumat Asas & Bio', () => {
    cy.get('#fullName').clear().type('Ahmad Whisker');
    cy.get('#phone').clear().type('0123456789');
    cy.get('#bio').clear().type('Saya suka kucing comel dan mempunyai 3 ekor kucing.');

    // Klon window:alert untuk mengesahkan popup kejayaan
    const alertStub = cy.stub();
    cy.on('window:alert', alertStub);

    cy.get('#profileForm').submit().then(() => {
      expect(alertStub.getCall(0)).to.be.null; // Alert mungkin berlaku selepas async Firebase call selesai
    });

    // Tunggu sehingga alert berjaya dipaparkan
    cy.wrap(alertStub).should('have.been.calledWithMatch', /Profile successfully updated|successfully/);
  });

  it('Langkah 2: Tukar Kata Laluan (Sah)', () => {
    cy.get('#newPass').type('newpassword123');
    cy.get('#confirmPass').type('newpassword123');

    const alertStub = cy.stub();
    cy.on('window:alert', alertStub);

    cy.get('#profileForm').submit();

    cy.wrap(alertStub).should('have.been.calledWithMatch', /Profile successfully updated|successfully/);

    // Tukar semula kata laluan kepada yang asal (password123)
    // supaya tidak merosakkan ujian seterusnya dan pusingan ujian akan datang.
    cy.get('#newPass').type(validPassword);
    cy.get('#confirmPass').type(validPassword);
    cy.get('#profileForm').submit();
  });

  it('Langkah 3: Tukar Kata Laluan (Tidak Padan)', () => {
    cy.get('#newPass').type('newpassword123');
    cy.get('#confirmPass').type('passwordSalah');

    const alertStub = cy.stub();
    cy.on('window:alert', alertStub);

    cy.get('#profileForm').submit();

    cy.wrap(alertStub).should('have.been.calledWith', 'Passwords do not match!');
  });

  it('Langkah 4: Muat Naik Gambar Profil', () => {
    // Cipta fail imej mockup dalam memori untuk dimuat naik
    cy.get('#imageInput').selectFile({
      contents: Cypress.Buffer.from('file-content'),
      fileName: 'avatar_kucing.jpg',
      mimeType: 'image/jpeg',
    }, { force: true });

    // Semak jika preview latar belakang ditukar selepas fail dipilih
    cy.get('#imagePreview').should('have.attr', 'style').and('contain', 'data:image/jpeg;base64');
  });

  it('Langkah 5: Ujian Ciri Penjaga Kucing (Khusus Sitter)', () => {
    // Langkah ini hanya diuji jika pengguna mempunyai peranan sebagai Cat Sitter
    cy.get('body').then(($body) => {
      if ($body.find('#sitterFields:visible').length > 0) {
        cy.get('#bandar').clear().type('Kajang');
        cy.get('#negeri').clear().type('Selangor');
        cy.get('#rate').clear().type('15.00');

        // Menguji tandakan checkbox perkhidmatan
        cy.get('.service-checkbox[value="grooming"]').check();
        cy.get('.service-checkbox[value="boarding"]').check();
        cy.get('.service-checkbox[value="daycare"]').uncheck();

        // Tambah Blocked Date (Contoh: Tarikh esok)
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dateString = tomorrow.toISOString().split('T')[0];

        cy.get('#blockDateInput').type(dateString);
        cy.get('.add-date-btn').click();

        // Semak tarikh berjaya ditambah ke dalam senarai
        cy.get('#blockedDatesList').should('contain', dateString);

        // Hantar borang untuk simpan
        const alertStub = cy.stub();
        cy.on('window:alert', alertStub);
        cy.get('#profileForm').submit();
        cy.wrap(alertStub).should('have.been.calledWithMatch', /Profile successfully updated|successfully/);
      } else {
        cy.log('Pengguna semasa bukan Cat Sitter, melangkau bahagian tetapan sitter.');
      }
    });
  });

  it('Langkah 6: Tindakan Butang Batal (Cancel)', () => {
    cy.get('.btn-cancel').click();
    cy.url().should('include', '/view/mainmenu.php');
  });

});
