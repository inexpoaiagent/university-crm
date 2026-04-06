export default async function ApplicationDetail({ params }: { params: Promise<{ id: string }> }) { const { id } = await params; return <main className="p-8">Application ID: {id}</main>; }
