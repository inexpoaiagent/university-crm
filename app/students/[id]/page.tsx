export default async function StudentDetail({ params }: { params: Promise<{ id: string }> }) { const { id } = await params; return <main className="p-8">Student ID: {id}</main>; }
