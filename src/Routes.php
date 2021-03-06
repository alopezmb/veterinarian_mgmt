<?php declare(strict_types=1);

return [
    [
        'GET',
        '/',
        'Felican\Controllers\BackupController#showBackupForm'
    ],
    [
        'GET',
        '/home',
        'Felican\Controllers\HomepageController#show'
    ],
    [
        'GET',
        '/submit',
        'Felican\Submission\Presentation\SubmissionController#show'
    ],
    [
        'POST',
        '/submit',
        'Felican\Submission\Presentation\SubmissionController#submit'
    ],
    [
        'GET',
        '/register',
        'Felican\User\Presentation\RegistrationController#show'
    ],
    [
        'POST',
        '/register',
        'Felican\User\Presentation\RegistrationController#register'
    ],
    [
        'GET',
        '/login',
        'Felican\User\Presentation\LoginController#show'
    ],
    [
        'POST',
        '/login',
        'Felican\User\Presentation\LoginController#logIn'
    ],
    [
        'GET',
        '/client/new',
        'Felican\Controllers\ClientController#newClientForm'
    ],
    [
        'POST',
        '/client/new',
        'Felican\Controllers\ClientController#createClient'
    ],
    [
        'GET',
        '/client/edit/{id:[0-9]+}',
        'Felican\Controllers\ClientController#editClientForm'
    ],
    [
        'POST',
        '/client/edit/{id:[0-9]+}',
        'Felican\Controllers\ClientController#editClient'
    ],
    [
        'GET',
        '/clients',
        'Felican\Controllers\ClientController#listClients'
    ],
    [
        'GET',
        '/client/{id:[0-9]+}',
        'Felican\Controllers\ClientController#showClient'
    ],
    [
        'POST',
        '/client/delete/{id:[0-9]+}',
        'Felican\Controllers\ClientController#deleteClient'
    ],
    [
        'POST',
        '/client/{id:[0-9]+}/debt',
        'Felican\Controllers\ClientController#saveDebt'
    ],
    [
        'POST',
        '/client/{id:[0-9]+}/debtHistory',
        'Felican\Controllers\ClientController#saveDebtHistory'
    ],
    [
        'GET',
        '/pets',
        'Felican\Controllers\PetController#listPets'
    ],
    [
        'GET',
        '/pet/{id:[0-9]+}',
        'Felican\Controllers\PetController#showPet'
    ],
    [
        'GET',
        '/pet/new/{ownerId:[0-9]+}',
        'Felican\Controllers\PetController#newPetForm'
    ],
    [
        'POST',
        '/pet/new/{ownerId:[0-9]+}',
        'Felican\Controllers\PetController#createOrEditPet'
    ],
    [
        'GET',
        '/pet/edit/{id:[0-9]+}',
        'Felican\Controllers\PetController#editPetForm'
    ],
    [
        'POST',
        '/pet/edit/{id:[0-9]+}',
        'Felican\Controllers\PetController#createOrEditPet'
    ],
    [
        'POST',
        '/pet/delete/{id:[0-9]+}',
        'Felican\Controllers\PetController#deletePet'
    ],
    [
        'POST',
        '/pet/{id:[0-9]+}/history',
        'Felican\Controllers\PetController#savePetHistory'
    ],
    [
        'POST',
        '/pet/{id:[0-9]+}/observations',
        'Felican\Controllers\PetController#savePetObservations'
    ],
    [
        'POST',
        '/pet/reminder',
        'Felican\Controllers\PetController#savePetReminders'
    ],
    [
        'POST',
        '/pet/{id:[0-9]+}/iguala',
        'Felican\Controllers\PetController#updateIguala'
    ],
    [
        'GET',
        '/mail',
        'Felican\Controllers\MailController#showMailForm'
    ],
    [
        'POST',
        '/mail',
        'Felican\Controllers\MailController#sendMailToClients'
    ],
    [
        'GET',
        '/avisos',
        'Felican\Controllers\AvisosController#showAvisosForm'
    ],
    [
        'POST',
        '/avisos',
        'Felican\Controllers\AvisosController#makeAvisosPDF'
    ],
    [
        'GET',
        '/backup',
        'Felican\Controllers\BackupController#showBackupForm'
    ],
    [
        'POST',
        '/backup',
        'Felican\Controllers\BackupController#performDBUpload'
    ]
];