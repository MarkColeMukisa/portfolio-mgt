export type PortfolioTag = {
    id: number;
    name: string;
    project_count?: number;
};

export type PortfolioProject = {
    id: number;
    title: string;
    description: string;
    description_preview: string;
    image_url: string;
    thumbnail_url: string;
    responsive_images: {
        mobile: string;
        tablet: string;
        desktop: string;
    };
    tags: PortfolioTag[];
    author: {
        id: number;
        name: string;
        avatar: string | null;
    };
    can: {
        delete: boolean;
    };
};

export type ScrollCollection<T> = {
    data: T[];
    current_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
};
