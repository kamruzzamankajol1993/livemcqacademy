<?php

namespace App\Imports;

use App\Models\McqQuestion;
use App\Models\Institute;
use App\Models\Board;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Models\ClassDepartment;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Topic;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class McqQuestionImport implements ToModel, WithHeadingRow, WithDrawings
{
    protected $drawings;

    public function __construct($drawings = [])
    {
        $this->drawings = $drawings;
    }

    public function drawings()
    {
        return $this->drawings;
    }

    public function model(array $row)
    {
        if (empty($row['mcq_type'])) return null;

        $type = strtolower(trim($row['mcq_type']));
        $isImage = ($type === 'image');

        // --- এতিম ডাটা হ্যান্ডেলিং (ইংলিশ না পেলে বাংলা দিয়ে চেক) ---
        $category_id = $this->getIdByName(Category::class, $row['category_name'] ?? null, 'english_name', 'bangla_name');
        $class_id    = $this->getIdByName(SchoolClass::class, $row['class_name'] ?? null, 'name_en', 'name_bn');
        $section_id  = $this->getIdByName(Section::class, $row['section_name'] ?? null, 'name_en', 'name_bn');
        
        $dept_id = null;
        if (!empty($row['department_name']) && $class_id) {
            $deptName = trim($row['department_name']);
            // প্রথমে ইংলিশ তারপর বাংলা চেক করে না পেলে নতুন তৈরি করবে
            $dept = ClassDepartment::where('name_en', $deptName)->orWhere('name_bn', $deptName)->first();
            if (!$dept) {
                $dept = ClassDepartment::create(['name_en' => $deptName, 'name_bn' => $deptName, 'slug' => Str::slug($deptName), 'status' => 1]);
            }
            $dept_id = $dept->id;
            $dept->classes()->syncWithoutDetaching([$class_id]);
        }

        $subject_id = null;
        if (!empty($row['subject_name']) && $class_id) {
            $subName = trim($row['subject_name']);
            $subject = Subject::where('name_en', $subName)->orWhere('name_bn', $subName)->first();
            if (!$subject) {
                $subject = Subject::create(['name_en' => $subName, 'name_bn' => $subName, 'slug' => Str::slug($subName), 'status' => 1]);
            }
            $subject_id = $subject->id;
            $subject->classes()->syncWithoutDetaching([$class_id]);
        }

        $chapter_id = null;
        if (!empty($row['chapter_name']) && $subject_id && $class_id) {
            $chapName = trim($row['chapter_name']);
            $chapter = Chapter::where(function($q) use ($chapName) {
                $q->where('name_en', $chapName)->orWhere('name_bn', $chapName);
            })->where('subject_id', $subject_id)->where('class_id', $class_id)->first();

            if (!$chapter) {
                $chapter = Chapter::create([
                    'name_en' => $chapName, 'name_bn' => $chapName, 
                    'subject_id' => $subject_id, 'class_id' => $class_id, 
                    'slug' => Str::slug($chapName.'-'.$class_id), 'status' => 1
                ]);
            }
            $chapter_id = $chapter->id;
        }

        $topic_id = null;
        if (!empty($row['topic_name']) && $chapter_id) {
            $topicName = trim($row['topic_name']);
            $topic = Topic::where(function($q) use ($topicName) {
                $q->where('name_en', $topicName)->orWhere('name_bn', $topicName);
            })->where('chapter_id', $chapter_id)->first();

            if (!$topic) {
                $topic = Topic::create([
                    'name_en' => $topicName, 'name_bn' => $topicName, 
                    'chapter_id' => $chapter_id, 'slug' => Str::slug($topicName.'-'.$chapter_id), 'status' => 1
                ]);
            }
            $topic_id = $topic->id;
        }

        $inst_ids = $this->processMultiData(Institute::class, $row['institute_names'] ?? '');
        $brd_ids = $this->processMultiData(Board::class, $row['board_names'] ?? '');

        $images = $this->processExcelImages($row);

        return new McqQuestion([
            'category_id'         => $category_id,
            'class_id'            => $class_id,
            'class_department_id' => $dept_id,
            'subject_id'          => $subject_id,
            'chapter_id'          => $chapter_id,
            'topic_id'            => $topic_id,
            'section_id'          => $section_id,
            'institute_ids'       => $inst_ids,
            'board_ids'           => $brd_ids,
            'mcq_type'            => $type,
            'question'            => $isImage ? null : ($row['question'] ?? null),
            'question_img'        => $images['question'] ?? null,
            'option_1'            => $isImage ? null : ($row['option_1'] ?? null),
            'option_1_img'        => $images['option_1'] ?? null,
            'option_2'            => $isImage ? null : ($row['option_2'] ?? null),
            'option_2_img'        => $images['option_2'] ?? null,
            'option_3'            => $isImage ? null : ($row['option_3'] ?? null),
            'option_3_img'        => $images['option_3'] ?? null,
            'option_4'            => $isImage ? null : ($row['option_4'] ?? null),
            'option_4_img'        => $images['option_4'] ?? null,
            'answer'              => $row['answer'],
            'tags'                => !empty($row['tags']) ? array_map('trim', explode(',', $row['tags'])) : null,
            'short_description'   => $row['short_description'] ?? null,
            'upload_type'         => $row['upload_type'] ?? 'subject_wise',
            'status'              => $row['status'] ?? 1,
        ]);
    }

    private function getIdByName($model, $name, $enCol, $bnCol) {
        if (empty($name)) return null;
        $val = trim($name);
        // প্রথমে ইংলিশ নাম দিয়ে চেক, না পেলে বাংলা নাম দিয়ে চেক
        $res = $model::where($enCol, $val)->orWhere($bnCol, $val)->first();
        return $res ? $res->id : null;
    }

    private function processMultiData($model, $data) {
        if (empty($data)) return [];
        $ids = [];
        foreach (explode(',', $data) as $val) {
            $name = trim($val);
            $record = $model::where('name_en', $name)->orWhere('name_bn', $name)->first();
            if (!$record) {
                $record = $model::create(['name_en' => $name, 'name_bn' => $name, 'slug' => Str::slug($name), 'status' => 1]);
            }
            $ids[] = $record->id;
        }
        return $ids;
    }

    private function processExcelImages($row) {
        $imagePaths = [];
        $columns = ['I' => 'question', 'J' => 'option_1', 'K' => 'option_2', 'L' => 'option_3', 'M' => 'option_4'];
        foreach ($this->drawings as $drawing) {
            $coords = $drawing->getCoordinates();
            $column = preg_replace('/[0-9]/', '', $coords);
            if (array_key_exists($column, $columns)) {
                if ($drawing instanceof MemoryDrawing) {
                    ob_start(); call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                    $content = ob_get_contents(); ob_end_clean();
                    $path = 'uploads/mcq/mcq_' . Str::random(10) . '.png';
                    if (!File::isDirectory(public_path('uploads/mcq'))) File::makeDirectory(public_path('uploads/mcq'), 0777, true);
                    File::put(public_path($path), $content);
                    $imagePaths[$columns[$column]] = $path;
                }
            }
        }
        return $imagePaths;
    }
}