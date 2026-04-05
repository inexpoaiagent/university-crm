export default async function UniversityDetail({ params }: { params: Promise<{ id: string }> }) { const { id } = await params; return <main className="p-8">University ID: {id}</main>; }
