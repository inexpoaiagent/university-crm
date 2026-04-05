import { Student, University } from '@prisma/client';

export function scoreUniversity(student: Student, university: University): number {
  let score = 0;
  if (student.fieldOfStudy && university.programs.some((program) => program.toLowerCase().includes(student.fieldOfStudy!.toLowerCase()))) score += 40;
  if (student.englishLevel && university.language?.toLowerCase().includes(student.englishLevel.toLowerCase())) score += 20;
  if (student.budget && university.tuitionMax && student.budget >= university.tuitionMax) score += 20;
  if (student.gpa && student.gpa >= 2.5) score += 20;
  return score;
}
