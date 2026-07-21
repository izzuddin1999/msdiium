<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->string('binding', 80)->nullable()->after('name');
            $table->index(['form_template_id', 'binding']);
        });

        foreach (DB::table('form_templates')->pluck('id') as $templateId) {
            if (! DB::table('form_fields')->where('form_template_id', $templateId)->whereNotNull('binding')->exists()) {
                $this->insertStandardFields((int) $templateId);
            }
        }
    }

    private function insertStandardFields(int $templateId): void
    {
        $now = now();
        $definitions = [
            ['Document Type','document_type','select','Document identity',1,true,'document_types'], ['Status','status','select','Document identity',1,true,'document_statuses'], ['Title','title','text','Document identity',1,true,null],
            ['Official Reference Number','reference_number','text','Document identity',1,false,null], ['Main Topic','topic_category','select','Classification',1,false,'main_topics'], ['Sub Topic','subtopic_id','select','Classification',1,false,'subtopics'],
            ['Owner Unit','owner_unit','select','Ownership and access',1,true,'departments'], ['Owner / Reporting Officer','owner_report','text','Ownership and access',1,false,null], ['Creator','created_by','select','Ownership and access',1,true,'users'],
            ['Access Scope','access_scope','select','Ownership and access',1,true,'access_scopes'], ['Set as Circular','is_circular','checkbox','Ownership and access',1,false,null], ['Publicly Visible','public_flag','checkbox','Ownership and access',1,false,null],
            ['Content','content','textarea','Content and validity',3,true,null], ['Effective Date','effective_date','date','Content and validity',1,false,null], ['Expiry Date','expiry_date','date','Content and validity',1,false,null],
            ['Controlled Source File','file','file','Content and validity',1,false,null], ['Remarks','remarks','textarea','Content and validity',3,false,null],
        ];
        foreach ($definitions as $index => [$label,$binding,$type,$section,$width,$required,$source]) {
            DB::table('form_fields')->insert(['form_template_id'=>$templateId,'label'=>$label,'name'=>$binding,'binding'=>$binding,'type'=>$type,'section'=>$section,'width'=>$width,'is_required'=>$required,'data_source'=>$source,'sort_order'=>($index+1)*10,'created_at'=>$now,'updated_at'=>$now]);
        }
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->dropIndex(['form_template_id', 'binding']);
            $table->dropColumn('binding');
        });
    }
};
