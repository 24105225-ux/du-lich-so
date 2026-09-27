@extends('layouts.app')

@section('title', 'Chương trình trải nghiệm')

@section('content')
<section class="section">
    <div class="container">
        <div class="section__heading">
            <div>
                <span class="eyebrow">
                    DANH MỤC
                </span>

                <h1>
                    Chương trình trải nghiệm giáo dục
                </h1>
            </div>

            <span>
                {{ $programs->total() }}
                chương trình
            </span>
        </div>

        <form
            method="GET"
            action="{{ route('programs.index') }}"
            class="filter-box"
        >
            <div class="form-group">
                <label for="keyword">
                    Từ khóa
                </label>

                <input
                    id="keyword"
                    type="text"
                    name="keyword"
                    value="{{ $filters['keyword'] ?? '' }}"
                    maxlength="120"
                    placeholder="Ví dụ: lịch sử, sinh thái..."
                >
            </div>

            <div class="form-group">
                <label for="education_level">
                    Cấp học
                </label>

                <select
                    id="education_level"
                    name="education_level"
                >
                    <option value="">
                        Tất cả
                    </option>

                    <option
                        value="TH"
                        @selected(
                            ($filters['education_level'] ?? null)
                            === 'TH'
                        )
                    >
                        Tiểu học
                    </option>

                    <option
                        value="THCS"
                        @selected(
                            ($filters['education_level'] ?? null)
                            === 'THCS'
                        )
                    >
                        THCS
                    </option>

                    <option
                        value="THPT"
                        @selected(
                            ($filters['education_level'] ?? null)
                            === 'THPT'
                        )
                    >
                        THPT
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="min_price">
                    Giá từ
                </label>

                <input
                    id="min_price"
                    type="number"
                    name="min_price"
                    min="0"
                    value="{{ $filters['min_price'] ?? '' }}"
                >
            </div>

            <div class="form-group">
                <label for="max_price">
                    Giá đến
                </label>

                <input
                    id="max_price"
                    type="number"
                    name="max_price"
                    min="0"
                    value="{{ $filters['max_price'] ?? '' }}"
                >
            </div>

            <div class="form-group">
                <label for="sort">
                    Sắp xếp
                </label>

                <select
                    id="sort"
                    name="sort"
                >
                    <option value="newest">
                        Mới nhất
                    </option>

                    <option
                        value="price_asc"
                        @selected(
                            ($filters['sort'] ?? null)
                            === 'price_asc'
                        )
                    >
                        Giá tăng dần
                    </option>

                    <option
                        value="price_desc"
                        @selected(
                            ($filters['sort'] ?? null)
                            === 'price_desc'
                        )
                    >
                        Giá giảm dần
                    </option>
                </select>
            </div>

            <div class="filter-actions">
                <button
                    class="button"
                    type="submit"
                >
                    Lọc dữ liệu
                </button>

                <a
                    class="button button--secondary"
                    href="{{ route('programs.index') }}"
                >
                    Xóa bộ lọc
                </a>
            </div>
        </form>

        <div class="card-grid">
            @forelse ($programs as $program)
                <x-program-card
                    :program="$program"
                />
            @empty
                <div class="empty-state">
                    Không tìm thấy chương trình phù hợp.
                </div>
            @endforelse
        </div>

        <div class="pagination-wrap">
            {{ $programs->links() }}
        </div>
    </div>
</section>
@endsection
