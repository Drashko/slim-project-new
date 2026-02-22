export default function PublicRedirectPage() {
    return null;
}

export async function getServerSideProps() {
    return {
        redirect: {
            destination: '/',
            permanent: false,
        },
    };
}
