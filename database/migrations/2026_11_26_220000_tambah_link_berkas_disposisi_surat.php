<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (!Schema::hasColumn('disposisi_surat', 'link_berkas')) {
                // Link PDF surat (Google Drive / OneDrive / dll), menggantikan upload file untuk surat baru
                $t->string('link_berkas', 500)->nullable()->after('link_daring');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (Schema::hasColumn('disposisi_surat', 'link_berkas')) {
                $t->dropColumn('link_berkas');
            }
        });
    }
};
