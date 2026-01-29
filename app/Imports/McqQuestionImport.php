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
use Illuminate\Support\Str;

class McqQuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Basic Validation: Question is mandatory
        if (!isset($row['question']) || empty($row['question'])) {
            return null;
        }

        // --- A. HELPER: First Or Create Logic ---

        // 1. Institute
        $institute_id = null;
        if (!empty($row['institute_name'])) {
            $instName = trim($row['institute_name']);
            $institute = Institute::firstOrCreate(
                ['name_en' => $instName],
                ['name_bn' => $instName, 'slug' => Str::slug($instName), 'status' => 1]
            );
            $institute_id = $institute->id;
        }

        // 2. Board
        $board_id = null;
        if (!empty($row['board_name'])) {
            $boardName = trim($row['board_name']);
            $board = Board::firstOrCreate(
                ['name_en' => $boardName],
                ['name_bn' => $boardName, 'slug' => Str::slug($boardName), 'status' => 1]
            );
            $board_id = $board->id;
        }

        // 3. Academic Year
        $year_id = null;
        if (!empty($row['year_name'])) {
            $yearName = trim($row['year_name']);
            $year = AcademicYear::firstOrCreate(
                ['name_en' => $yearName],
                ['name_bn' => $yearName, 'slug' => Str::slug($yearName), 'status' => 1]
            );
            $year_id = $year->id;
        }

        // 4. Category
        $category_id = null;
        if (!empty($row['category_name'])) {
            $catName = trim($row['category_name']);
            $category = Category::firstOrCreate(
                ['name_en' => $catName],
                ['name_bn' => $catName, 'slug' => Str::slug($catName), 'status' => 1]
            );
            $category_id = $category->id;
        }

        // 5. Class (Mandatory if provided)
        $class_id = null;
        if (!empty($row['class_name'])) {
            $className = trim($row['class_name']);
            $class = SchoolClass::firstOrCreate(
                ['name_en' => $className],
                ['name_bn' => $className, 'slug' => Str::slug($className), 'status' => 1]
            );
            $class_id = $class->id;
        }

        // 6. Department (Optional but linked to Class)
        $dept_id = null;
        if (!empty($row['department_name']) && $class_id) {
            $deptName = trim($row['department_name']);
            $dept = ClassDepartment::firstOrCreate(
                ['name_en' => $deptName],
                ['name_bn' => $deptName, 'slug' => Str::slug($deptName), 'status' => 1]
            );
            $dept_id = $dept->id;

            // Link Department to Class (Pivot) if not already linked
            $dept->classes()->syncWithoutDetaching([$class_id]);
        }

        // 7. Subject (Linked to Class & Dept)
        $subject_id = null;
        if (!empty($row['subject_name']) && $class_id) {
            $subName = trim($row['subject_name']);
            // Subject Check (Name only first, then link)
            $subject = Subject::firstOrCreate(
                ['name_en' => $subName],
                ['name_bn' => $subName, 'slug' => Str::slug($subName), 'status' => 1]
            );
            $subject_id = $subject->id;

            // Link Subject to Class (Pivot)
            $subject->classes()->syncWithoutDetaching([$class_id]);

            // Link Subject to Department (Pivot) if department exists
            if ($dept_id) {
                $subject->departments()->syncWithoutDetaching([$dept_id]);
            }
        }

        // 8. Chapter (Dependent on Subject & Class)
        $chapter_id = null;
        if (!empty($row['chapter_name']) && $subject_id && $class_id) {
            $chapName = trim($row['chapter_name']);
            
            // Chapter must match Name, Subject AND Class
            $chapter = Chapter::firstOrCreate(
                [
                    'name_en' => $chapName,
                    'subject_id' => $subject_id,
                    'class_id' => $class_id
                ],
                [
                    'name_bn' => $chapName,
                    'slug' => Str::slug($chapName . '-' . $class_id), // Ensure unique slug logic
                    'status' => 1
                ]
            );
            $chapter_id = $chapter->id;
        }

        // 9. Topic (Dependent on Chapter)
        $topic_id = null;
        if (!empty($row['topic_name']) && $chapter_id) {
            $topicName = trim($row['topic_name']);
            
            $topic = Topic::firstOrCreate(
                [
                    'name_en' => $topicName,
                    'chapter_id' => $chapter_id
                ],
                [
                    'name_bn' => $topicName,
                    'slug' => Str::slug($topicName . '-' . $chapter_id),
                    'status' => 1
                ]
            );
            $topic_id = $topic->id;
        }

        // --- B. Prepare Tags ---
        $tags = null;
        if (isset($row['tags']) && !empty($row['tags'])) {
            $tags = array_map('trim', explode(',', $row['tags']));
        }

        // --- C. Create MCQ ---
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