<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Courrier;
use App\Models\PieceJointe;
use App\Models\Service;
use Core\Helpers;
use PDOException;
use RuntimeException;

class CourrierController extends BaseController
{
    private const MAX_ATTACHMENT_SIZE = 10_485_760; // 10 MB
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/zip',
        'application/x-zip-compressed',
        'application/vnd.rar',
        'text/plain',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/tiff',
    ];
    private const UPLOAD_SUBDIRECTORY = 'courriers';

    public function index(): void
    {
        $filters = $this->collectFilters();

        $courriers = Courrier::all($filters);
        $services = Service::all();

        $this->render('courriers/index', [
            'title'     => 'Courriers',
            'courriers' => $courriers,
            'services'  => $services,
            'filters'   => $filters,
            'types'     => Courrier::TYPES,
            'statuts'   => Courrier::STATUTS,
        ]);
    }

    public function print(): void
    {
        $filters = $this->collectFilters();

        $courriers = Courrier::all($filters);
        $services = Service::all();

        $previousLayout = $this->layout;
        $this->layout = 'layout-print';

        $this->render('courriers/print', [
            'title'     => 'Etat des courriers',
            'courriers' => $courriers,
            'filters'   => $filters,
            'services'  => $services,
            'types'     => Courrier::TYPES,
            'statuts'   => Courrier::STATUTS,
        ]);

        $this->layout = $previousLayout;
    }

    private function collectFilters(): array
    {
        return [
            'type'             => $this->input('type'),
            'statut'           => $this->input('statut'),
            'service_cible_id' => $this->input('service_cible_id'),
            'search'           => $this->input('search'),
            'date_start'       => $this->input('date_start'),
            'date_end'         => $this->input('date_end'),
        ];
    }

    public function create(): void
    {
        $services = Service::active();

        $this->render('courriers/form', [
            'title'            => 'Enregistrer un courrier',
            'services'         => $services,
            'courrier'         => null,
            'attachments'      => [],
            'types'            => Courrier::TYPES,
            'priorites'        => Courrier::PRIORITES,
            'confidentialites' => Courrier::CONFIDENTIALITES,
            'statuts'          => Courrier::STATUTS,
            'action'           => Helpers::route('courrier', 'store'),
        ]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $data = $this->courrierPayload();
        $data['created_by'] = (int) ($this->userId() ?? 0);

        if ($data['created_by'] < 1) {
            Helpers::flash('error', 'Session expiree. Veuillez vous reconnecter.');
            $this->redirect('auth', 'login');
            return;
        }

        $errors = $this->validateCourrier($data);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('courrier', 'create');
            return;
        }

        try {
            $courrierId = Courrier::create($data);
            $uploadErrors = $this->processAttachments($courrierId);

            if (!empty($uploadErrors)) {
                Helpers::flash('warning', implode(' ', $uploadErrors));
            }

            Helpers::flash('success', 'Courrier enregistre avec succes.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Reference deja utilisee ou donnees invalides.']);
            Helpers::flash('error', 'Erreur lors de la creation du courrier.');
            $this->redirect('courrier', 'create');
            return;
        }

        $this->redirect('courrier', 'index');
    }

    public function edit(): void
    {
        $id = (int) $this->input('id');
        $courrier = Courrier::find($id);

        if (!$courrier) {
            Helpers::flash('error', 'Courrier introuvable.');
            $this->redirect('courrier', 'index');
            return;
        }

        $services = Service::all();
        $attachments = PieceJointe::forCourrier($id);

        $this->render('courriers/form', [
            'title'            => 'Modifier le courrier',
            'courrier'         => $courrier,
            'attachments'      => $attachments,
            'services'         => $services,
            'types'            => Courrier::TYPES,
            'priorites'        => Courrier::PRIORITES,
            'confidentialites' => Courrier::CONFIDENTIALITES,
            'statuts'          => Courrier::STATUTS,
            'action'           => Helpers::route('courrier', 'update'),
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');
        $courrier = Courrier::find($id);

        if (!$courrier) {
            Helpers::flash('error', 'Courrier introuvable.');
            $this->redirect('courrier', 'index');
            return;
        }

        $data = $this->courrierPayload();
        $data['created_by'] = (int) ($courrier['created_by'] ?? ($this->userId() ?? 0));

        if ($data['created_by'] < 1) {
            Helpers::flash('error', 'Session expiree. Veuillez vous reconnecter.');
            $this->redirect('auth', 'login');
            return;
        }

        $errors = $this->validateCourrier($data, true);

        if (!empty($errors)) {
            Helpers::storeOld($_POST);
            Helpers::errors($errors);
            $this->redirect('courrier', 'edit', ['id' => $id]);
            return;
        }

        $messages = [];

        try {
            Courrier::update($id, $data);

            $toDelete = isset($_POST['attachments_to_delete']) && is_array($_POST['attachments_to_delete'])
                ? array_map('intval', $_POST['attachments_to_delete'])
                : [];

            if (!empty($toDelete)) {
                $deleteErrors = $this->removeAttachments($id, $toDelete);
                if (!empty($deleteErrors)) {
                    $messages[] = implode(' ', $deleteErrors);
                }
            }

            $uploadErrors = $this->processAttachments($id);
            if (!empty($uploadErrors)) {
                $messages[] = implode(' ', $uploadErrors);
            }

            if (!empty($messages)) {
                Helpers::flash('warning', implode(' ', $messages));
            }

            Helpers::flash('success', 'Courrier mis a jour.');
        } catch (PDOException $exception) {
            Helpers::storeOld($_POST);
            Helpers::errors(['base' => 'Impossible de mettre a jour ce courrier.']);
            Helpers::flash('error', 'Erreur lors de la mise a jour.');
            $this->redirect('courrier', 'edit', ['id' => $id]);
            return;
        }

        $this->redirect('courrier', 'index');
    }

    public function delete(): void
    {
        $this->requirePost();
        $this->validateCsrfOrFail();

        $id = (int) $this->input('id');

        try {
            $existingAttachments = PieceJointe::forCourrier($id);
            if (!empty($existingAttachments)) {
                $attachmentIds = array_map('intval', array_column($existingAttachments, 'id'));
                $deleteWarnings = $this->removeAttachments($id, $attachmentIds);
                if (!empty($deleteWarnings)) {
                    Helpers::flash('warning', implode(' ', $deleteWarnings));
                }
            }

            if (Courrier::delete($id)) {
                Helpers::flash('success', 'Courrier supprime.');
            } else {
                Helpers::flash('error', 'Courrier introuvable.');
            }
        } catch (PDOException $exception) {
            Helpers::flash('error', 'Suppression impossible: des elements lies existent.');
        }

        $this->redirect('courrier', 'index');
    }

    public function download(): void
    {
        $id = (int) $this->input('id');
        $pieceJointe = PieceJointe::find($id);

        if (!$pieceJointe) {
            Helpers::flash('error', 'Piece jointe introuvable.');
            $this->redirect('courrier', 'index');
            return;
        }

        $filePath = $this->buildAbsolutePath($pieceJointe['chemin']);
        if (!is_file($filePath) || !is_readable($filePath)) {
            Helpers::flash('error', 'Le fichier de la piece jointe est manquant.');
            $this->redirect('courrier', 'edit', ['id' => (int) $pieceJointe['courrier_id']]);
            return;
        }

        $this->streamDownload($pieceJointe, $filePath);
    }

    private function courrierPayload(): array
    {
        return [
            'type'              => (string) $this->input('type'),
            'ref'               => (string) $this->input('ref'),
            'objet'             => (string) $this->input('objet'),
            'expediteur'        => $this->input('expediteur') ?: null,
            'destinataire'      => $this->input('destinataire') ?: null,
            'date_reception'    => $this->input('date_reception') ?: null,
            'date_envoi'        => $this->input('date_envoi') ?: null,
            'priorite'          => (string) $this->input('priorite', 'NORMALE'),
            'confidentialite'   => (string) $this->input('confidentialite', 'INTERNE'),
            'service_source_id' => $this->input('service_source_id') ? (int) $this->input('service_source_id') : null,
            'service_cible_id'  => $this->input('service_cible_id') ? (int) $this->input('service_cible_id') : null,
            'statut'            => (string) $this->input('statut', 'ENREGISTRE'),
            'echeance'          => $this->input('echeance') ?: null,
        ];
    }

    private function validateCourrier(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!in_array($data['type'], Courrier::TYPES, true)) {
            $errors['type'] = 'Type invalide.';
        }

        if (empty($data['ref'])) {
            $errors['ref'] = 'La reference est obligatoire.';
        } elseif (strlen($data['ref']) > 50) {
            $errors['ref'] = 'La reference ne doit pas depasser 50 caracteres.';
        }

        if (empty($data['objet'])) {
            $errors['objet'] = 'L\'objet est obligatoire.';
        }

        if (!in_array($data['priorite'], Courrier::PRIORITES, true)) {
            $errors['priorite'] = 'Priorite invalide.';
        }

        if (!in_array($data['confidentialite'], Courrier::CONFIDENTIALITES, true)) {
            $errors['confidentialite'] = 'Confidentialite invalide.';
        }

        if (!in_array($data['statut'], Courrier::STATUTS, true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        if ($data['type'] === 'ENTRANT' && empty($data['date_reception'])) {
            $errors['date_reception'] = 'La date de reception est obligatoire pour un courrier entrant.';
        }

        if ($data['type'] === 'SORTANT' && empty($data['date_envoi'])) {
            $errors['date_envoi'] = 'La date d\'envoi est obligatoire pour un courrier sortant.';
        }

        if (!empty($data['echeance']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $data['echeance'])) {
            $errors['echeance'] = 'Format de date d\'echeance invalide (AAAA-MM-JJ).';
        }

        if (!empty($data['date_reception']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $data['date_reception'])) {
            $errors['date_reception'] = 'Format de date de reception invalide (AAAA-MM-JJ).';
        }

        if (!empty($data['date_envoi']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $data['date_envoi'])) {
            $errors['date_envoi'] = 'Format de date d\'envoi invalide (AAAA-MM-JJ).';
        }

        return $errors;
    }

    private function processAttachments(int $courrierId): array
    {
        if (empty($_FILES['pieces_jointes']) || !is_array($_FILES['pieces_jointes']['name'])) {
            return [];
        }

        $files = $_FILES['pieces_jointes'];
        $errors = [];
        $count = count($files['name']);

        for ($index = 0; $index < $count; $index++) {
            $errorCode = isset($files['error'][$index]) ? (int) $files['error'][$index] : UPLOAD_ERR_NO_FILE;
            if ($errorCode === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $originalName = (string) ($files['name'][$index] ?? '');

            if ($errorCode !== UPLOAD_ERR_OK) {
                $errors[] = sprintf('Echec du televersement pour %s (code %d).', $this->sanitizeDisplayName($originalName), $errorCode);
                continue;
            }

            $size = (int) ($files['size'][$index] ?? 0);
            $tmpName = $files['tmp_name'][$index] ?? '';

            if ($size <= 0 || !is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
                $errors[] = sprintf('Le fichier %s est invalide.', $this->sanitizeDisplayName($originalName));
                continue;
            }

            if ($size > self::MAX_ATTACHMENT_SIZE) {
                $errors[] = sprintf('Le fichier %s depasse la taille maximale autorisee (10 Mo).', $this->sanitizeDisplayName($originalName));
                continue;
            }

            $cleanOriginal = $this->sanitizeFilename($originalName);
            $extension = strtolower((string) pathinfo($cleanOriginal, PATHINFO_EXTENSION));

            try {
                $storedName = $this->generateStoredFilename($extension);
            } catch (RuntimeException $exception) {
                $errors[] = sprintf('Generation du nom de fichier impossible pour %s.', $this->sanitizeDisplayName($originalName));
                continue;
            }

            $relativeDirectory = self::UPLOAD_SUBDIRECTORY . '/' . date('Y') . '/' . date('m');
            if (!$this->ensureDirectoryExists($relativeDirectory)) {
                $errors[] = sprintf('Creation du dossier de stockage impossible pour %s.', $this->sanitizeDisplayName($originalName));
                continue;
            }

            $targetPath = $this->buildAbsolutePath($relativeDirectory . '/' . $storedName);

            if (!move_uploaded_file($tmpName, $targetPath)) {
                $errors[] = sprintf('Televersement impossible pour %s.', $this->sanitizeDisplayName($originalName));
                continue;
            }

            if (!is_readable($targetPath)) {
                $errors[] = sprintf('Fichier televerse illisible pour %s.', $this->sanitizeDisplayName($originalName));
                @unlink($targetPath);
                continue;
            }

            $mime = $this->detectMimeType($targetPath, (string) ($files['type'][$index] ?? 'application/octet-stream'));
            if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
                $errors[] = sprintf('Type de fichier non autorise pour %s.', $this->sanitizeDisplayName($originalName));
                @unlink($targetPath);
                continue;
            }

            $relativePath = $relativeDirectory . '/' . $storedName;

            PieceJointe::create($courrierId, [
                'nom_fichier' => $cleanOriginal,
                'chemin'      => $relativePath,
                'taille'      => $size,
                'mime'        => $mime,
            ]);
        }

        return $errors;
    }

    private function removeAttachments(int $courrierId, array $attachmentIds): array
    {
        $errors = [];

        foreach ($attachmentIds as $attachmentId) {
            $attachmentId = (int) $attachmentId;
            if ($attachmentId < 1) {
                continue;
            }

            $attachment = PieceJointe::find($attachmentId);
            if (!$attachment || (int) $attachment['courrier_id'] !== $courrierId) {
                continue;
            }

            $absolutePath = $this->buildAbsolutePath((string) $attachment['chemin']);
            if (is_file($absolutePath) && !@unlink($absolutePath)) {
                $errors[] = sprintf('Impossible de supprimer le fichier %s.', $this->sanitizeDisplayName((string) $attachment['nom_fichier']));
                continue;
            }

            PieceJointe::delete($attachmentId);
        }

        return $errors;
    }

    private function ensureDirectoryExists(string $relativeDir): bool
    {
        $absolute = $this->buildAbsolutePath($relativeDir);
        if (is_dir($absolute)) {
            return true;
        }

        return mkdir($absolute, 0775, true) || is_dir($absolute);
    }

    private function buildAbsolutePath(string $relativePath): string
    {
        $normalized = str_replace(['\\', '..'], ['/', ''], $relativePath);
        $normalized = ltrim($normalized, '/');

        return rtrim(BASE_PATH . '/public/uploads', "/\\") . '/' . $normalized;
    }

    private function detectMimeType(string $filePath, string $fallback): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $filePath) ?: null;
                finfo_close($finfo);

                if (!empty($mime)) {
                    return $mime;
                }
            }
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($filePath);
            if (!empty($mime)) {
                return $mime;
            }
        }

        return $fallback ?: 'application/octet-stream';
    }

    private function generateStoredFilename(string $extension): string
    {
        try {
            $random = bin2hex(random_bytes(16));
        } catch (\Exception $exception) {
            throw new RuntimeException('Unable to generate secure filename.', 0, $exception);
        }

        return $extension !== '' ? $random . '.' . $extension : $random;
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = trim($filename);
        $filename = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
        $filename = preg_replace('/[^A-Za-z0-9._\-]+/', '_', $filename) ?? 'piece_jointe';
        $filename = trim($filename, '_');

        if ($filename === '') {
            $filename = 'piece_jointe';
        }

        if (strlen($filename) > 180) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if ($extension !== '') {
                $base = substr($filename, 0, max(1, 180 - (strlen($extension) + 1)));
                $filename = rtrim($base, '.') . '.' . $extension;
            } else {
                $filename = substr($filename, 0, 180);
            }
        }

        return $filename;
    }

    private function sanitizeDisplayName(string $filename): string
    {
        $clean = trim($filename);
        if ($clean === '') {
            return 'fichier';
        }

        return $clean;
    }

    private function streamDownload(array $pieceJointe, string $filePath): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($pieceJointe['mime'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . rawurlencode((string) $pieceJointe['nom_fichier']) . '"');
        header('Content-Length: ' . (string) filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filePath);
        exit;
    }
}







