<?php

namespace App\Imports;

use App\Models\McqQuestion;
use App\Models\Institute;
use App\Models\Board;
use App\Models\AcademicYear;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Models\ClassDepartment;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Topic;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class McqQuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // প্রশ্ন না থাকলে স্কিপ করবে
        if (!isset($row['question']) || empty($row['question'])) {
            return null;
        }

        // --- 1. Find IDs & Validation ---

        // Institute Check
        $institute_id = null;
        if (!empty($row['institute_name'])) {
            $institute = Institute::where('name_en', trim($row['institute_name']))->first();
            if (!$institute) {
                throw new Exception("Institute '{$row['institute_name']}' not found! Please add data in Institute table first.");
            }
            $institute_id = $institute->id;
        }

        // Board Check
        $board_id = null;
        if (!empty($row['board_name'])) {
            $board = Board::where('name_en', trim($row['board_name']))->first();
            if (!$board) {
                throw new Exception("Board '{$row['board_name']}' not found! Please add data in Board table first.");
            }
            $board_id = $board->id;
        }

        // Year Check
        $year_id = null;
        if (!empty($row['year_name'])) {
            $year = AcademicYear::where('name_en', trim($row['year_name']))->first();
            if (!$year) {
                throw new Exception("Academic Year '{$row['year_name']}' not found! Please add data in Academic Year table first.");
            }
            $year_id = $year->id;
        }

        // Category Check
        $category_id = null;
        if (!empty($row['category_name'])) {
            $category = Category::where('name_en', trim($row['category_name']))->first();
            if (!$category) {
                throw new Exception("Category '{$row['category_name']}' not found! Please add data in Category table first.");
            }
            $category_id = $category->id;
        }

        // Class Check (Mandatory handled logic)
        $class_id = null;
        if (!empty($row['class_name'])) {
            $class = SchoolClass::where('name_en', trim($row['class_name']))->first();
            if (!$class) {
                throw new Exception("Class '{$row['class_name']}' not found! Please add data in Class table first.");
            }
            $class_id = $class->id;
        }

        // Department Check
        $dept_id = null;
        if (!empty($row['department_name'])) {
            $dept = ClassDepartment::where('name_en', trim($row['department_name']))->first();
            if (!$dept) {
                throw new Exception("Department '{$row['department_name']}' not found! Please add data in Class Department table first.");
            }
            $dept_id = $dept->id;
        }

        // Subject Check
        $subject_id = null;
        if (!empty($row['subject_name'])) {
            $subject = Subject::where('name_en', trim($row['subject_name']))->first();
            if (!$subject) {
                throw new Exception("Subject '{$row['subject_name']}' not found! Please add data in Subject table first.");
            }
            $subject_id = $subject->id;
        }

        // Chapter Check
        $chapter_id = null;
        if (!empty($row['chapter_name'])) {
            $chapter = Chapter::where('name_en', trim($row['chapter_name']))->first();
            if (!$chapter) {
                throw new Exception("Chapter '{$row['chapter_name']}' not found! Please add data in Chapter table first.");
            }
            $chapter_id = $chapter->id;
        }

        // Topic Check
        $topic_id = null;
        if (!empty($row['topic_name'])) {
            $topic = Topic::where('name_en', trim($row['topic_name']))->first();
            if (!$topic) {
                throw new Exception("Topic '{$row['topic_name']}' not found! Please add data in Topic table first.");
            }
            $topic_id = $topic->id;
        }

        // --- 2. Handle Tags ---
        $tags = null;
        if (isset($row['tags']) && !empty($row['tags'])) {
            $tags = array_map('trim', explode(',', $row['tags']));
        }

        // --- 3. Create MCQ ---
        return new McqQuestion([
            'institute_id'      => $institute_id,
            'board_id'          => $board_id,
            'year_id'           => $year_id,
            'category_id'       => $category_id,
            'class_id'          => $class_id,
            'class_department_id' => $dept_id,
            'subject_id'        => $subject_id,
            'chapter_id'        => $chapter_id,
            'topic_id'          => $topic_id,

            'question'          => $row['question'],
            'option_1'          => $row['option_1'],
            'option_2'          => $row['option_2'],
            'option_3'          => $row['option_3'],
            'option_4'          => $row['option_4'],
            'answer'            => $row['answer'],
            
            'tags'              => $tags,
            'short_description' => $row['short_description'] ?? null,
            'upload_type'       => $row['upload_type'] ?? 'subject_wise',
            'status'            => $row['status'] ?? 1,
        ]);
    }
}